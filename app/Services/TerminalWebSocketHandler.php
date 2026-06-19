<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\CommandLog;
use App\Models\JitSession;
use GuzzleHttp\Psr7\Request as Psr7Request;
use Illuminate\Support\Facades\Crypt;
use Ratchet\RFC6455\Handshake\RequestVerifier;
use Ratchet\RFC6455\Handshake\ServerNegotiator;
use Ratchet\RFC6455\Messaging\CloseFrameChecker;
use Ratchet\RFC6455\Messaging\Frame;
use Ratchet\RFC6455\Messaging\MessageBuffer;
use Ratchet\RFC6455\Messaging\MessageInterface;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use React\Socket\TcpServer;

/**
 * Handles WebSocket connections for interactive SSH PTY terminal sessions.
 *
 * Uses ratchet/rfc6455 for RFC 6455 WebSocket protocol compliance
 * (handshake, frame encoding/decoding, masking, ping/pong, close frames).
 *
 * Uses React\ChildProcess for non-blocking SSH process management.
 *
 * @internal Experimental feature — authorised lab use only.
 */
class TerminalWebSocketHandler
{
    /** @var array<string, array{process: Process|null, tempKeyPath: ?string, session: JitSession|null, logRef: ?string, openedAt: int, timer: mixed, messageBuffer: MessageBuffer|null, stderrBuffer: string, stdoutReceived: bool, stderrReceived: bool, lineBuffer: string}> */
    private array $connections = [];

    private PtyTerminalService $ptyService;

    private LoopInterface $loop;

    private int $connectionCount = 0;

    /**
     * In-memory nonce store for single-use token enforcement.
     * Maps nonce → true. Consumed on first use.
     * Cleared when the Artisan process exits.
     *
     * @var array<string, true>
     */
    private array $usedNonces = [];

    public function __construct(PtyTerminalService $ptyService, LoopInterface $loop)
    {
        $this->ptyService = $ptyService;
        $this->loop = $loop;
    }

    /**
     * Attach this handler to a ReactPHP TCP server.
     * Each incoming TCP connection goes through HTTP upgrade → WebSocket → SSH PTY.
     */
    public function attach(TcpServer $server): void
    {
        $server->on('connection', function (ConnectionInterface $conn) {
            $this->onTcpConnect($conn);
        });
    }

    /**
     * Terminate all active SSH processes and close all WebSocket connections.
     * Called on Artisan shutdown (SIGTERM/SIGINT).
     */
    public function shutdownAll(): void
    {
        foreach ($this->connections as $id => $state) {
            if ($state['timer'] !== null) {
                $this->loop->cancelTimer($state['timer']);
            }
            $this->ptyService->cleanupProcess($state['process'], $state['tempKeyPath']);
            $this->logAudit($state['session'], 'interactive_terminal_closed', $state['openedAt']);
        }

        $this->connections = [];
    }

    // ─── TCP / HTTP Handshake ───────────────────────────────────────────────

    /**
     * Handle a new TCP connection. Buffer data until we receive a complete HTTP
     * upgrade request, then perform the WebSocket handshake.
     */
    private function onTcpConnect(ConnectionInterface $conn): void
    {
        $connId = 'c'.(++$this->connectionCount);
        $httpBuffer = '';
        $this->connections[$connId] = [
            'process' => null,
            'tempKeyPath' => null,
            'session' => null,
            'logRef' => null,
            'openedAt' => time(),
            'timer' => null,
            'messageBuffer' => null,
            'stderrBuffer' => '',
            'stdoutReceived' => false,
            'stderrReceived' => false,
            'lineBuffer' => '',
        ];

        $conn->on('data', function (string $data) use ($conn, $connId, &$httpBuffer) {
            // If messageBuffer is already set, we're in WebSocket mode — forward to it
            if (isset($this->connections[$connId]['messageBuffer']) && $this->connections[$connId]['messageBuffer'] !== null) {
                $this->connections[$connId]['messageBuffer']->onData($data);
                return;
            }

            // Accumulate HTTP handshake request
            $httpBuffer .= $data;

            // Wait for end of HTTP headers
            if (! str_contains($httpBuffer, "\r\n\r\n")) {
                if (strlen($httpBuffer) > 8192) {
                    $conn->close(); // Oversized header — drop
                }
                return;
            }

            $this->performHandshake($conn, $connId, $httpBuffer);
        });

        $conn->on('close', function () use ($connId) {
            $this->onWsClose($connId);
        });

        $conn->on('error', function (\Exception $e) use ($connId) {
            $this->writeLine("Connection error [{$connId}]: ".$e->getMessage());
        });
    }

    /**
     * Parse the HTTP upgrade request and perform the WebSocket handshake using
     * ratchet/rfc6455 ServerNegotiator for full RFC 6455 compliance.
     */
    private function performHandshake(ConnectionInterface $conn, string $connId, string $httpBuffer): void
    {
        try {
            $psrRequest = $this->parseHttpRequest($httpBuffer);
        } catch (\Throwable $e) {
            $conn->write("HTTP/1.1 400 Bad Request\r\nContent-Length: 0\r\n\r\n");
            $conn->close();
            return;
        }

        $negotiator = new ServerNegotiator(
            new RequestVerifier,
            new \GuzzleHttp\Psr7\HttpFactory
        );

        $response = $negotiator->handshake($psrRequest);

        if ($response->getStatusCode() !== 101) {
            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            $conn->write("HTTP/1.1 {$statusCode} Error\r\nContent-Length: ".strlen($body)."\r\n\r\n{$body}");
            $conn->close();
            return;
        }

        // Send 101 Switching Protocols response
        $responseBody = $this->buildHttpResponseString($response);
        $conn->write($responseBody);

        // Switch to WebSocket frame mode
        $this->setupWebSocket($conn, $connId);
    }

    /**
     * Parse raw HTTP request bytes into a PSR-7 Request object.
     */
    private function parseHttpRequest(string $raw): Psr7Request
    {
        // Split headers from body
        $parts = explode("\r\n\r\n", $raw, 2);
        $headerLines = explode("\r\n", $parts[0]);
        $requestLine = array_shift($headerLines);

        // Parse request line: "GET /path HTTP/1.1"
        $segments = explode(' ', $requestLine, 3);
        $method = $segments[0] ?? 'GET';
        $uri = $segments[1] ?? '/';

        // Parse headers
        $headers = [];
        foreach ($headerLines as $line) {
            $colonPos = strpos($line, ':');
            if ($colonPos !== false) {
                $name = trim(substr($line, 0, $colonPos));
                $value = trim(substr($line, $colonPos + 1));
                $headers[$name] = $value;
            }
        }

        return new Psr7Request($method, $uri, $headers, $parts[1] ?? '');
    }

    /**
     * Convert a PSR-7 Response into a raw HTTP response string for sending over TCP.
     */
    private function buildHttpResponseString(\Psr\Http\Message\ResponseInterface $response): string
    {
        $statusLine = sprintf(
            "HTTP/%s %d %s\r\n",
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );

        $headers = '';
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $headers .= "{$name}: {$value}\r\n";
            }
        }

        return $statusLine.$headers."\r\n";
    }

    // ─── WebSocket Frame Mode ───────────────────────────────────────────────

    /**
     * Set up WebSocket message handling using ratchet/rfc6455 MessageBuffer.
     * After this point, all TCP data is processed as WebSocket frames.
     */
    private function setupWebSocket(ConnectionInterface $conn, string $connId): void
    {
        $messageBuffer = new MessageBuffer(
            new CloseFrameChecker,
            function (MessageInterface $message) use ($conn, $connId) {
                $this->onWsMessage($conn, $connId, $message->getPayload());
            },
            function (Frame $frame) use ($conn, $connId) {
                $this->onWsControl($conn, $connId, $frame);
            },
            true, // expectMask (client frames must be masked)
            null,
            null,
            null,
            function (string $rawFrame) use ($conn) {
                // Sender callback: writes raw WebSocket frame bytes to TCP socket
                $conn->write($rawFrame);
            }
        );

        $this->connections[$connId]['messageBuffer'] = $messageBuffer;
    }

    /**
     * Handle a complete WebSocket message (text or binary frame).
     * The first message must be the auth token; subsequent messages are SSH stdin data.
     */
    private function onWsMessage(ConnectionInterface $conn, string $connId, string $payload): void
    {
        $state = $this->connections[$connId] ?? null;
        if ($state === null) {
            return;
        }

        // First message = auth token
        if ($state['process'] === null && $state['session'] === null) {
            $this->handleAuthToken($conn, $connId, $payload);
            return;
        }

        // Subsequent messages = SSH stdin input (keystrokes from xterm.js)
        // Route through policy-aware writer instead of forwarding directly.
        $this->writeToSsh($conn, $connId, $payload);
    }

    /**
     * Handle WebSocket control frames (ping, pong, close).
     */
    private function onWsControl(ConnectionInterface $conn, string $connId, Frame $frame): void
    {
        $state = $this->connections[$connId] ?? null;

        if ($frame->getOpcode() === Frame::OP_CLOSE) {
            // Close handshake: echo back the close frame, then close TCP connection
            if (isset($state['messageBuffer']) && $state['messageBuffer'] !== null) {
                $closeFrame = $state['messageBuffer']->newCloseFrame(Frame::CLOSE_NORMAL);
                $state['messageBuffer']->sendFrame($closeFrame);
            }
            $conn->close();
            return;
        }

        if ($frame->getOpcode() === Frame::OP_PING) {
            // Respond to ping with pong (RFC 6455 §5.5.3)
            if (isset($state['messageBuffer']) && $state['messageBuffer'] !== null) {
                $pong = $state['messageBuffer']->newFrame($frame->getPayload(), true, Frame::OP_PONG);
                $state['messageBuffer']->sendFrame($pong);
            }
            return;
        }

        // OP_PONG: no action needed
    }

    // ─── Auth Token Validation ──────────────────────────────────────────────

    /**
     * Validate the WebSocket auth token and spawn the SSH process if valid.
     */
    private function handleAuthToken(ConnectionInterface $conn, string $connId, string $token): void
    {
        try {
            $payload = Crypt::decryptString($token);
            $data = json_decode($payload, true);

            if (! is_array($data)) {
                throw new \RuntimeException('Invalid token format.');
            }

            // Check expiry
            if (! isset($data['exp']) || $data['exp'] < time()) {
                throw new \RuntimeException('Token expired.');
            }

            // Check nonce (single-use enforcement)
            $nonce = $data['nonce'] ?? '';
            if ($nonce === '' || isset($this->usedNonces[$nonce])) {
                throw new \RuntimeException('Token already used or invalid.');
            }
            $this->usedNonces[$nonce] = true;

            // Load session
            $session = JitSession::with('targetServer')->find($data['sid'] ?? 0);
            if (! $session) {
                throw new \RuntimeException('Session not found.');
            }

            // Verify user ownership
            if ((int) ($data['uid'] ?? 0) !== (int) $session->user_id) {
                throw new \RuntimeException('User mismatch.');
            }

            // Verify session is usable
            if (! $session->isUsable()) {
                $this->logAudit($session, 'interactive_terminal_denied');
                throw new \RuntimeException('Session is not active.');
            }

            // Verify target server
            if (! $session->targetServer || ! $session->targetServer->is_active) {
                $this->logAudit($session, 'interactive_terminal_denied');
                throw new \RuntimeException('Target server is inactive.');
            }

            $this->connections[$connId]['session'] = $session;

            // Resolve credentials and start SSH process
            $credential = $this->ptyService->resolveCredential($session);
            $result = $this->ptyService->startSshProcess($session, $credential, $this->loop);

            $this->connections[$connId]['process'] = $result['process'];
            $this->connections[$connId]['tempKeyPath'] = $result['tempKeyPath'];
            $this->connections[$connId]['logRef'] = $result['logRef'];

            // Immediately clear credential from memory
            unset($credential);

            // Log debug info for process start
            $this->writeLine("[{$connId}] SSH process starting:");
            $this->writeLine("  Command label: {$result['debugInfo']['command_label']}");
            $this->writeLine("  Auth type: {$result['debugInfo']['auth_type']}");
            $this->writeLine("  Target: {$result['debugInfo']['target']}");
            $this->writeLine("  SSH options: {$result['debugInfo']['ssh_options']}");
            $this->writeLine("  ENV has PATH: ".($result['debugInfo']['has_path'] ? 'YES' : 'NO'));
            $this->writeLine("  ENV has HOME: ".($result['debugInfo']['has_home'] ? 'YES' : 'NO'));
            $this->writeLine("  ENV has SSHPASS: ".($result['debugInfo']['has_sshpass'] ? 'YES' : 'NO'));

            $this->attachProcessListeners($conn, $connId, $result['process']);
            $this->startSessionExpiryMonitor($conn, $connId);

            $this->logAudit($session, 'interactive_terminal_opened');
            $this->writeLine("[{$connId}] Terminal opened — {$result['logRef']}");
        } catch (\Throwable $e) {
            $this->sendWsText($conn, $connId, json_encode([
                'type' => 'error',
                'message' => 'Authentication failed.',
            ]));
            $this->sendWsClose($conn, $connId, Frame::CLOSE_POLICY, 'Authentication failed');
            $conn->close();
            $this->writeLine("[{$connId}] Auth failed: ".$e->getMessage());
        }
    }

    // ─── SSH Process I/O Bridging ───────────────────────────────────────────

    /**
     * Wire up SSH process stdout/stderr to WebSocket output, and detect process exit.
     * Includes debug logging for process lifecycle and stderr forwarding.
     */
    private function attachProcessListeners(ConnectionInterface $conn, string $connId, Process $process): void
    {
        // stdout → WebSocket (binary frame for terminal data with ANSI escape codes)
        $process->stdout->on('data', function (string $data) use ($conn, $connId) {
            // Mark that we received stdout data for debug logging
            if (isset($this->connections[$connId])) {
                $this->connections[$connId]['stdoutReceived'] = true;
            }
            $this->sendWsBinary($conn, $connId, $data);
        });

        // stderr → WebSocket (merged with stdout for terminal display) + debug logging
        $process->stderr->on('data', function (string $data) use ($conn, $connId) {
            // Mark that we received stderr data for debug logging
            if (isset($this->connections[$connId])) {
                $this->connections[$connId]['stderrReceived'] = true;
                // Buffer stderr for logging (first 500 chars only to avoid memory issues)
                $buffered = $this->connections[$connId]['stderrBuffer'];
                if (strlen($buffered) < 500) {
                    $this->connections[$connId]['stderrBuffer'] .= $data;
                }
            }

            // Forward stderr to terminal (SSH auth errors, host key warnings, etc.)
            $this->sendWsBinary($conn, $connId, $data);
        });

        // Process exit → log debug info → close WebSocket
        $process->on('exit', function ($exitCode, $termSignal) use ($conn, $connId) {
            $state = $this->connections[$connId] ?? null;
            if ($state) {
                // Log comprehensive debug info
                $this->writeLine("[{$connId}] SSH process exited:");
                $this->writeLine("  Exit code: ".($exitCode ?? 'null'));
                $this->writeLine("  Signal: ".($termSignal ?? 'null'));
                $this->writeLine("  stdout received: ".($state['stdoutReceived'] ? 'YES' : 'NO'));
                $this->writeLine("  stderr received: ".($state['stderrReceived'] ? 'YES' : 'NO'));

                // Log sanitized stderr (first 500 chars)
                if ($state['stderrReceived'] && $state['stderrBuffer'] !== '') {
                    $sanitized = $this->sanitizeStderr($state['stderrBuffer']);
                    $this->writeLine("  stderr (sanitized): {$sanitized}");
                }

                // Send exit info to client
                $this->sendWsText($conn, $connId, json_encode([
                    'type' => 'exit',
                    'code' => $exitCode,
                    'signal' => $termSignal,
                ]));
                $this->sendWsClose($conn, $connId, Frame::CLOSE_NORMAL, 'SSH session ended');
            }
            $conn->close();
        });
    }

    /**
     * Start a periodic timer that checks if the JIT session has expired or been revoked.
     * If the session becomes non-usable, terminate the SSH process and close the WebSocket.
     */
    private function startSessionExpiryMonitor(ConnectionInterface $conn, string $connId): void
    {
        $timer = $this->loop->addPeriodicTimer(30, function () use ($conn, $connId) {
            $state = $this->connections[$connId] ?? null;
            if ($state === null || $state['session'] === null) {
                return;
            }

            // Refresh session status from database
            $session = JitSession::find($state['session']->id);
            if (! $session || ! $session->isUsable()) {
                $this->writeLine("[{$connId}] Session expired/revoked — closing terminal.");
                $this->sendWsText($conn, $connId, json_encode([
                    'type' => 'close',
                    'reason' => 'session_expired',
                ]));
                $this->sendWsClose($conn, $connId, Frame::CLOSE_NORMAL, 'Session expired');
                $conn->close();
            }
        });

        $this->connections[$connId]['timer'] = $timer;
    }

    // ─── Command Policy Enforcement ─────────────────────────────────────────

    /**
     * Buffer keystrokes into a command line and enforce the command-block policy
     * before allowing Enter to reach the SSH process.
     *
     * Printable characters are forwarded to SSH stdin immediately (preserving
     * natural editing and tab-completion) while also being accumulated in the
     * per-connection lineBuffer.  When Enter is pressed the accumulated line is
     * checked against the block-list; a blocked command causes Ctrl+U to be
     * written to SSH (clearing the remote shell line) and a red ANSI error to
     * be sent back to the xterm.js client.
     *
     * **Known limitation:** This line-buffer approach cannot intercept commands
     * typed inside interactive sub-programs (e.g. `python3`, `mysql` REPL,
     * `vim`, `nano`).  Those keystrokes are consumed by the sub-program and
     * never appear as a plain command line on stdin.  Full coverage for
     * sub-programs requires session recording for post-hoc review.
     */
    private function writeToSsh(ConnectionInterface $conn, string $connId, string $payload): void
    {
        $state = &$this->connections[$connId];

        if ($state === null || $state['process'] === null || ! $state['process']->isRunning()) {
            return;
        }

        $len = strlen($payload);

        for ($i = 0; $i < $len; $i++) {
            $byte = ord($payload[$i]);

            // ── Escape sequences (arrow keys, function keys, etc.) ─────────
            // Forward without accumulating so that cursor movement, Home/End,
            // and other navigation keys continue to work in the remote shell.
            if ($byte === 27) {
                // Skip the full ANSI escape sequence (ESC [ … terminator or ESC O key)
                $j = $i + 1;
                if ($j < $len && ($payload[$j] === '[' || $payload[$j] === 'O')) {
                    $j++;
                    while ($j < $len && ord($payload[$j]) >= 32 && ord($payload[$j]) < 127) {
                        $j++;
                    }
                }
                // Forward the entire escape sequence to stdin as-is
                $state['process']->stdin->write(substr($payload, $i, $j - $i));
                $i = $j - 1; // will be incremented by the for-loop
                continue;
            }

            // ── Enter (CR or LF) ──────────────────────────────────────────
            if ($byte === 13 || $byte === 10) {
                $command = trim($state['lineBuffer']);
                $state['lineBuffer'] = '';

                if ($command !== '') {
                    $blockedReason = $this->checkCommandPolicy($state['session'], $command);

                    if ($blockedReason !== null) {
                        // Send Ctrl+U to SSH stdin — clears the remote shell line
                        $state['process']->stdin->write("\x15");

                        // Send red ANSI error message back to the xterm.js client
                        $errorMsg = "\r\n\x1b[31m⛔ BLOCKED: {$blockedReason}\x1b[0m\r\n";
                        $this->sendWsBinary($conn, $connId, $errorMsg);

                        $this->logBlockedCommand($state['session'], $command, $blockedReason);
                        // Do NOT forward Enter — the command must not execute.
                        continue;
                    }

                    $this->logAllowedCommand($state['session'], $command);
                }

                // Command allowed (or blank line) — forward Enter to stdin
                $state['process']->stdin->write($payload[$i]);
                continue;
            }

            // ── Backspace (DEL 127 / BS 8) ────────────────────────────────
            if ($byte === 127 || $byte === 8) {
                $state['lineBuffer'] = substr($state['lineBuffer'], 0, -1);
                $state['process']->stdin->write($payload[$i]);
                continue;
            }

            // ── Ctrl+U (clear line, byte 21) ──────────────────────────────
            if ($byte === 21) {
                $state['lineBuffer'] = '';
                $state['process']->stdin->write($payload[$i]);
                continue;
            }

            // ── Ctrl+C (byte 3) / Ctrl+D (byte 4) ─────────────────────────
            if ($byte === 3 || $byte === 4) {
                $state['lineBuffer'] = '';
                $state['process']->stdin->write($payload[$i]);
                continue;
            }

            // ── Printable characters (ASCII 32–126) ───────────────────────
            if ($byte >= 32 && $byte <= 126) {
                $state['lineBuffer'] .= $payload[$i];
                $state['process']->stdin->write($payload[$i]);
                continue;
            }

            // ── Other control characters (Tab, Ctrl+W, etc.) ──────────────
            // Forward to stdin so that shell features like tab-completion and
            // word-erase continue to work, but do not accumulate in the buffer.
            $state['process']->stdin->write($payload[$i]);
        }
    }

    /**
     * Check whether a command is allowed under the JIT command-block policy.
     *
     * Returns a human-readable reason string if the command is blocked,
     * or null if the command is permitted.
     */
    private function checkCommandPolicy(?JitSession $session, string $command): ?string
    {
        $blockedPatterns = [
            '/(?:^|[;&|]\s*)(?:sudo\s+)?(?:passwd|chpasswd|useradd|usermod|userdel)(?:\s|$)/i'
                => 'User account modification commands are not permitted.',
            '/(?:^|[;&|]\s*)(?:sudo\s+)?(?:reboot|shutdown|poweroff|halt)(?:\s|$)/i'
                => 'System power/reboot commands are not permitted.',
            '/(?:^|[;&|]\s*)(?:sudo\s+)?systemctl\s+(?:reboot|poweroff)(?:\s|$)/i'
                => 'systemctl reboot/poweroff is not permitted.',
            '/(?:^|[;&|]\s*)(?:sudo\s+)?(?:mkfs(?:\.\w+)?|fdisk|parted)(?:\s|$)/i'
                => 'Disk/partition modification commands are not permitted.',
            '/(?:^|[;&|]\s*)(?:sudo\s+)?dd\s+.*\bif=/i'
                => 'Raw disk write (dd if=…) is not permitted.',
            '/\brm\s+-rf\s+\/(?:\s|$)/i'
                => 'Recursive deletion of root filesystem is not permitted.',
        ];

        foreach ($blockedPatterns as $pattern => $reason) {
            if (preg_match($pattern, $command)) {
                return $reason;
            }
        }

        return null;
    }

    /**
     * Persist a blocked-command record to the command_logs table.
     * Wrapped in try/catch so that audit failure never crashes the WebSocket server.
     */
    private function logBlockedCommand(?JitSession $session, string $command, string $reason): void
    {
        try {
            CommandLog::create([
                'jit_session_id' => $session?->id,
                'user_id' => $session?->user_id,
                'command' => $command,
                'status' => CommandLog::STATUS_BLOCKED,
                'blocked_reason' => $reason,
                'executed_at' => now(),
                'metadata' => ['source' => 'pty-xterm'],
            ]);
        } catch (\Throwable $e) {
            $this->writeLine('Command log failed (blocked): '.$e->getMessage());
        }
    }

    /**
     * Persist an allowed-command record to the command_logs table.
     * Wrapped in try/catch so that audit failure never crashes the WebSocket server.
     */
    private function logAllowedCommand(?JitSession $session, string $command): void
    {
        try {
            CommandLog::create([
                'jit_session_id' => $session?->id,
                'user_id' => $session?->user_id,
                'command' => $command,
                'status' => CommandLog::STATUS_ALLOWED,
                'executed_at' => now(),
                'metadata' => ['source' => 'pty-xterm'],
            ]);
        } catch (\Throwable $e) {
            $this->writeLine('Command log failed (allowed): '.$e->getMessage());
        }
    }

    // ─── WebSocket Close / Cleanup ──────────────────────────────────────────

    /**
     * Handle WebSocket connection close. Terminate SSH process and clean up resources.
     */
    private function onWsClose(string $connId): void
    {
        $state = $this->connections[$connId] ?? null;
        if ($state === null) {
            return;
        }

        // Cancel session expiry timer
        if ($state['timer'] !== null) {
            $this->loop->cancelTimer($state['timer']);
        }

        // Terminate SSH process and clean up temp key file
        $this->ptyService->cleanupProcess($state['process'], $state['tempKeyPath']);

        // Log audit event
        $this->logAudit($state['session'], 'interactive_terminal_closed', $state['openedAt']);

        $this->writeLine("[{$connId}] Terminal closed.");

        unset($this->connections[$connId]);
    }

    // ─── WebSocket Frame Helpers ────────────────────────────────────────────

    /**
     * Send a WebSocket text frame to the client.
     */
    private function sendWsText(ConnectionInterface $conn, string $connId, string $data): void
    {
        $state = $this->connections[$connId] ?? null;
        if (isset($state['messageBuffer']) && $state['messageBuffer'] !== null) {
            $state['messageBuffer']->sendMessage($data, true, false);
        }
    }

    /**
     * Send a WebSocket binary frame to the client (used for terminal output).
     * Binary frames avoid UTF-8 validation issues with raw SSH output.
     */
    private function sendWsBinary(ConnectionInterface $conn, string $connId, string $data): void
    {
        $state = $this->connections[$connId] ?? null;
        if (isset($state['messageBuffer']) && $state['messageBuffer'] !== null) {
            $state['messageBuffer']->sendMessage($data, true, true);
        }
    }

    /**
     * Send a WebSocket close frame to the client.
     */
    private function sendWsClose(ConnectionInterface $conn, string $connId, int $code, string $reason = ''): void
    {
        $state = $this->connections[$connId] ?? null;
        if (isset($state['messageBuffer']) && $state['messageBuffer'] !== null) {
            $closeFrame = $state['messageBuffer']->newCloseFrame($code, $reason);
            $state['messageBuffer']->sendFrame($closeFrame);
        }
    }

    // ─── Audit Logging ──────────────────────────────────────────────────────

    /**
     * Create an audit log entry for a terminal event.
     * No secrets are included in metadata — only session IDs and duration.
     *
     * Note: WebSocket-side audit calls may lack HTTP request context,
     * so ip_address/user_agent may be null.
     */
    private function logAudit(?JitSession $session, string $action, ?int $openedAt = null): void
    {
        if (! $session) {
            return;
        }

        $metadata = [
            'jit_session_id' => $session->id,
            'target_server_id' => $session->target_server_id,
        ];

        if ($action === 'interactive_terminal_closed' && $openedAt !== null) {
            $metadata['duration_seconds'] = time() - $openedAt;
        }

        try {
            AuditLog::create([
                'actor_id' => $session->user_id,
                'action' => $action,
                'target_type' => 'JitSession',
                'target_id' => $session->id,
                'description' => "Terminal event: {$action} for JIT session #{$session->id}.",
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'metadata' => $metadata,
            ]);
        } catch (\Throwable $e) {
            // Audit failure should not crash the WebSocket server
            $this->writeLine("Audit log failed: ".$e->getMessage());
        }
    }

    // ─── Console Output ─────────────────────────────────────────────────────

    private function writeLine(string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        fwrite(STDOUT, "[{$timestamp}] {$message}\n");
    }

    // ─── Stderr Sanitization ────────────────────────────────────────────────

    /**
     * Sanitize SSH stderr output for safe logging.
     * Removes potential secrets (passwords, keys) and truncates to 500 chars.
     */
    private function sanitizeStderr(string $stderr): string
    {
        // Truncate to 500 chars to avoid memory issues
        $truncated = substr($stderr, 0, 500);

        // Remove common sensitive patterns
        $patterns = [
            '/password[:\s]+[^\s]+/i' => 'password: [REDACTED]',
            '/passphrase[:\s]+[^\s]+/i' => 'passphrase: [REDACTED]',
            '/secret[:\s]+[^\s]+/i' => 'secret: [REDACTED]',
            '/token[:\s]+[^\s]+/i' => 'token: [REDACTED]',
            '/key[:\s]+[A-Za-z0-9+\/=]{20,}/i' => 'key: [REDACTED]',
        ];

        $sanitized = preg_replace(array_keys($patterns), array_values($patterns), $truncated);

        // Replace newlines with spaces for single-line logging
        $sanitized = str_replace(["\r\n", "\r", "\n"], ' ', $sanitized);

        // Add ellipsis if truncated
        if (strlen($stderr) > 500) {
            $sanitized .= '... [truncated]';
        }

        return $sanitized;
    }
}
