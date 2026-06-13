<?php

namespace App\Services;

use App\Models\JitSession;
use Illuminate\Support\Facades\Crypt;
use React\ChildProcess\Process;

/**
 * Manages SSH PTY process lifecycle for interactive terminal sessions.
 *
 * SECURITY:
 * - Password auth uses sshpass -e with SSHPASS env var (never in command-line args)
 * - Private key auth uses temporary files with 0600 permissions, deleted after process close
 * - Generated SSH command strings are NEVER logged — only opaque references are used
 * - Decrypted credentials are never stored in class properties beyond method scope
 *
 * @internal This service is experimental and intended for authorised lab use only.
 */
class PtyTerminalService
{
    /**
     * Resolve SSH credentials for a JIT session.
     *
     * Returns an array describing the authentication method:
     * - type: 'password' | 'private_key'
     * - username: the SSH login username
     * - credential: the decrypted password string or decoded private key PEM string
     *
     * Temporary credentials (from JIT session) take priority over target server credentials.
     *
     * @return array{type: string, username: string, credential: string}
     *
     * @throws \RuntimeException If no valid credential is available
     */
    public function resolveCredential(JitSession $session): array
    {
        $targetServer = $session->targetServer;

        // 1. Temporary credential from JIT session (highest priority)
        if ($session->hasCreatedTemporaryCredential()) {
            return [
                'type' => 'password',
                'username' => $session->temporary_username,
                'credential' => Crypt::decryptString($session->temporary_password_encrypted),
            ];
        }

        // 2. Target server stored credential
        if ($targetServer->auth_type === 'password' && $targetServer->hasPassword()) {
            return [
                'type' => 'password',
                'username' => $targetServer->ssh_username,
                'credential' => Crypt::decryptString($targetServer->ssh_password_encrypted),
            ];
        }

        if ($targetServer->auth_type === 'private_key' && $targetServer->hasPrivateKey()) {
            return [
                'type' => 'private_key',
                'username' => $targetServer->ssh_username,
                'credential' => Crypt::decryptString($targetServer->ssh_private_key_encrypted),
            ];
        }

        throw new \RuntimeException('No valid SSH credential available for this session.');
    }

    /**
     * Start an SSH PTY process using React\ChildProcess.
     *
     * The returned array contains:
     * - process: React\ChildProcess\Process (already started, with stdin/stdout/stderr streams)
     * - tempKeyPath: string|null (temporary private key file path, must be cleaned up)
     * - debugInfo: array (safe debug info: command label, env keys, options)
     *
     * IMPORTANT: The generated SSH command string is NEVER safe to log.
     * Only log the opaque reference string returned in the 'logRef' key.
     *
     * @param  JitSession  $session  The JIT session (must have targetServer loaded)
     * @param  array  $credential  Output from resolveCredential()
     * @param  \React\EventLoop\LoopInterface|null  $loop  ReactPHP event loop (null = default)
     * @return array{process: Process, tempKeyPath: ?string, logRef: string, debugInfo: array}
     */
    public function startSshProcess(JitSession $session, array $credential, $loop = null): array
    {
        $targetServer = $session->targetServer;
        $tempKeyPath = null;
        $env = [];

        // CRITICAL: Merge with current environment to preserve PATH, HOME, etc.
        // React\ChildProcess\Process REPLACES env when provided (not merges).
        $baseEnv = [];
        foreach ($_SERVER as $key => $value) {
            if (is_string($value) && ! str_starts_with($key, 'HTTP_')) {
                $baseEnv[$key] = $value;
            }
        }
        $baseEnv['PATH'] = $_ENV['PATH'] ?? getenv('PATH') ?: '/usr/bin:/bin';
        $baseEnv['HOME'] = $_ENV['HOME'] ?? getenv('HOME') ?: '/tmp';

        // Build SSH command (password never appears in command string)
        // For password mode: force password auth, disable pubkey to ensure sshpass works
        // For both modes: disable host key checking, enable keepalive
        $sshOptions = '-tt -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -o ServerAliveInterval=30 -o ServerAliveCountMax=3';

        if ($credential['type'] === 'password') {
            // sshpass -e reads password from SSHPASS environment variable
            // This ensures the password never appears in /proc/*/cmdline or ps aux
            // Force password auth and disable pubkey to ensure sshpass is used
            $sshOptions .= ' -o PreferredAuthentications=password -o PubkeyAuthentication=no';
            $command = sprintf(
                'sshpass -e ssh %s -p %d %s@%s',
                $sshOptions,
                $targetServer->port,
                escapeshellarg($credential['username']),
                escapeshellarg($targetServer->host)
            );
            $env = array_merge($baseEnv, ['SSHPASS' => $credential['credential']]);
        } elseif ($credential['type'] === 'private_key') {
            // Write decrypted key to a temporary file with 0600 permissions
            $tempKeyPath = tempnam(sys_get_temp_dir(), 'pam_jit_key_');
            file_put_contents($tempKeyPath, $credential['credential']);
            chmod($tempKeyPath, 0600);

            $command = sprintf(
                'ssh %s -i %s -p %d %s@%s',
                $sshOptions,
                escapeshellarg($tempKeyPath),
                $targetServer->port,
                escapeshellarg($credential['username']),
                escapeshellarg($targetServer->host)
            );
            $env = $baseEnv;
        } else {
            throw new \RuntimeException('Unsupported credential type: '.$credential['type']);
        }

        // Opaque reference for logging — NEVER log $command
        $logRef = "ssh session for JIT #{$session->id} to {$targetServer->name}";

        // Debug info (safe, no secrets)
        $debugInfo = [
            'command_label' => $credential['type'] === 'password' ? 'sshpass -e ssh -tt ...' : 'ssh -tt -i <tempkey> ...',
            'auth_type' => $credential['type'],
            'target' => sprintf('%s@%s:%d', $credential['username'], $targetServer->host, $targetServer->port),
            'env_keys' => array_keys($env),
            'has_path' => isset($env['PATH']),
            'has_home' => isset($env['HOME']),
            'has_sshpass' => isset($env['SSHPASS']),
            'ssh_options' => $sshOptions,
        ];

        $process = new Process($command, null, $env);
        $process->start($loop);

        // Immediately unset env to minimise credential lifetime in memory
        unset($env, $credential);

        return [
            'process' => $process,
            'tempKeyPath' => $tempKeyPath,
            'logRef' => $logRef,
            'debugInfo' => $debugInfo,
        ];
    }

    /**
     * Terminate an SSH process and clean up temporary resources.
     *
     * Safe to call multiple times. Always attempts to:
     * 1. Terminate the process (SIGTERM, then SIGKILL after 2 seconds)
     * 2. Delete the temporary key file (if any)
     */
    public function cleanupProcess(?Process $process, ?string $tempKeyPath): void
    {
        if ($process !== null) {
            try {
                if ($process->isRunning()) {
                    $process->terminate(SIGTERM);

                    // Give the process 2 seconds to exit gracefully before SIGKILL
                    $deadline = time() + 2;
                    while ($process->isRunning() && time() < $deadline) {
                        usleep(100000); // 100ms
                    }

                    if ($process->isRunning()) {
                        $process->terminate(SIGKILL);
                    }
                }
            } catch (\Throwable $e) {
                // Process may already be dead; ignore cleanup errors
            }

            try {
                if ($process->stdin !== null && $process->stdin->isWritable()) {
                    $process->stdin->close();
                }
            } catch (\Throwable $e) {
                // Ignore stream close errors
            }
        }

        if ($tempKeyPath !== null && file_exists($tempKeyPath)) {
            @unlink($tempKeyPath);
        }
    }
}
