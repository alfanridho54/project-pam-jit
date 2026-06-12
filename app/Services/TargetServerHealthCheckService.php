<?php

namespace App\Services;

use App\Models\TargetServer;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SSH2;
use Throwable;

class TargetServerHealthCheckService
{
    /**
     * TCP connection timeout in seconds.
     */
    private const TCP_TIMEOUT = 3;

    /**
     * SSH handshake + auth timeout in seconds.
     */
    private const SSH_TIMEOUT = 10;

    /**
     * Run a health check against the given target server.
     *
     * Returns a structured result array. Never includes secrets.
     *
     * @return array{status: string, tcp_ok: bool, ssh_ok: bool, latency_ms: int|null, message: string, checked_at: \Illuminate\Support\Carbon}
     */
    public function check(TargetServer $server): array
    {
        $checkedAt = now();

        // ── 1. TCP connectivity ──────────────────────────────────────────
        [$tcpOk, $tcpLatencyMs, $tcpError] = $this->tcpProbe($server->host, (int) $server->port);

        if (! $tcpOk) {
            return $this->result(
                status: 'tcp_failed',
                tcpOk: false,
                sshOk: false,
                latencyMs: null,
                message: 'TCP connection to port ' . $server->port . ' failed or timed out. ' . $tcpError,
                checkedAt: $checkedAt,
            );
        }

        // ── 2. Optional SSH authentication ──────────────────────────────
        if (blank($server->ssh_username)) {
            // No username configured – report TCP-only result
            return $this->result(
                status: 'tcp_open',
                tcpOk: true,
                sshOk: false,
                latencyMs: $tcpLatencyMs,
                message: 'TCP connection to SSH port succeeded. No SSH username configured for authentication check.',
                checkedAt: $checkedAt,
            );
        }

        // Check whether we have any credential to attempt SSH with
        $hasCredential = ($server->auth_type === 'password' && $server->hasPassword())
            || ($server->auth_type === 'private_key' && $server->hasPrivateKey());

        if (! $hasCredential) {
            return $this->result(
                status: 'tcp_open',
                tcpOk: true,
                sshOk: false,
                latencyMs: $tcpLatencyMs,
                message: 'TCP connection to SSH port succeeded. No stored credential available for SSH authentication check.',
                checkedAt: $checkedAt,
            );
        }

        // Try SSH auth
        try {
            $credential = $this->decryptCredential($server);
        } catch (DecryptException) {
            return $this->result(
                status: 'error',
                tcpOk: true,
                sshOk: false,
                latencyMs: $tcpLatencyMs,
                message: 'TCP connection succeeded but stored credential could not be decrypted. Re-save the server credential.',
                checkedAt: $checkedAt,
            );
        } catch (Throwable) {
            return $this->result(
                status: 'error',
                tcpOk: true,
                sshOk: false,
                latencyMs: $tcpLatencyMs,
                message: 'TCP connection succeeded but credential preparation failed.',
                checkedAt: $checkedAt,
            );
        }

        try {
            $ssh = new SSH2($server->host, $server->port, self::SSH_TIMEOUT);
            $ssh->setTimeout(self::SSH_TIMEOUT);

            if (! $ssh->login($server->ssh_username, $credential)) {
                return $this->result(
                    status: 'ssh_failed',
                    tcpOk: true,
                    sshOk: false,
                    latencyMs: $tcpLatencyMs,
                    message: 'TCP connection succeeded but SSH authentication failed. Check stored credential.',
                    checkedAt: $checkedAt,
                );
            }

            $ssh->disconnect();

            return $this->result(
                status: 'ssh_ok',
                tcpOk: true,
                sshOk: true,
                latencyMs: $tcpLatencyMs,
                message: 'TCP connection and SSH authentication both succeeded.',
                checkedAt: $checkedAt,
            );
        } catch (Throwable) {
            return $this->result(
                status: 'error',
                tcpOk: true,
                sshOk: false,
                latencyMs: $tcpLatencyMs,
                message: 'Health check failed due to a connection error during SSH handshake.',
                checkedAt: $checkedAt,
            );
        }
    }

    /**
     * Open a raw TCP socket to measure reachability and latency.
     *
     * @return array{bool, int|null, string}  [success, latency_ms, error_message]
     */
    private function tcpProbe(string $host, int $port): array
    {
        $startMs = hrtime(true);

        try {
            $socket = @fsockopen($host, $port, $errno, $errstr, self::TCP_TIMEOUT);

            if ($socket === false) {
                return [false, null, trim($errstr ?: 'Connection refused or timed out.')];
            }

            $latencyMs = (int) round((hrtime(true) - $startMs) / 1e6);
            fclose($socket);

            return [true, $latencyMs, ''];
        } catch (Throwable $e) {
            return [false, null, 'Unexpected error during TCP probe.'];
        }
    }

    /**
     * Decrypt the server's stored SSH credential (password or private key).
     * Never returns the raw secret in a way that would be logged.
     */
    private function decryptCredential(TargetServer $server): mixed
    {
        if ($server->auth_type === 'password') {
            return Crypt::decryptString($server->ssh_password_encrypted);
        }

        return PublicKeyLoader::loadPrivateKey(
            Crypt::decryptString($server->ssh_private_key_encrypted)
        );
    }

    /**
     * Build a structured health check result. No secrets included.
     *
     * @return array{status: string, tcp_ok: bool, ssh_ok: bool, latency_ms: int|null, message: string, checked_at: \Illuminate\Support\Carbon}
     */
    private function result(
        string $status,
        bool $tcpOk,
        bool $sshOk,
        ?int $latencyMs,
        string $message,
        \Illuminate\Support\Carbon $checkedAt,
    ): array {
        return compact('status', 'tcpOk', 'sshOk', 'latencyMs', 'message', 'checkedAt');
    }
}
