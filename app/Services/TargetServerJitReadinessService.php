<?php

namespace App\Services;

use App\Models\TargetServer;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SSH2;
use Throwable;

class TargetServerJitReadinessService
{
    /**
     * SSH connection timeout in seconds.
     */
    private const SSH_TIMEOUT = 10;

    /**
     * Commands to verify sudo NOPASSWD availability.
     *
     * @var array<string, string>
     */
    private const CHECKS = [
        'sudo_id_ok'    => 'sudo -n /usr/bin/id',
        'useradd_ok'    => 'sudo -n /usr/sbin/useradd --help >/dev/null 2>&1',
        'chpasswd_ok'   => 'sudo -n /usr/sbin/chpasswd --help >/dev/null 2>&1',
        'usermod_ok'    => 'sudo -n /usr/sbin/usermod --help >/dev/null 2>&1',
        'userdel_ok'    => 'sudo -n /usr/sbin/userdel --help >/dev/null 2>&1',
    ];

    /**
     * Run a JIT readiness check on the given target server.
     *
     * Verifies whether the server's SSH user has passwordless sudo access
     * for the user management commands required by temporary credential
     * provisioning.
     *
     * @return array{status: string, message: string, checkedAt: \Illuminate\Support\Carbon, details: array<string, bool>}
     */
    public function check(TargetServer $server): array
    {
        $checkedAt = now();

        // ── 1. Decrypt credential ───────────────────────────────────────
        try {
            $credential = $this->decryptCredential($server);
        } catch (DecryptException) {
            return $this->result(
                status: 'error',
                message: 'Readiness check failed due to an unexpected error.',
                checkedAt: $checkedAt,
                details: $this->emptyDetails(),
            );
        } catch (Throwable) {
            return $this->result(
                status: 'error',
                message: 'Readiness check failed due to an unexpected error.',
                checkedAt: $checkedAt,
                details: $this->emptyDetails(),
            );
        }

        // ── 2. SSH connection ───────────────────────────────────────────
        try {
            $ssh = new SSH2($server->host, $server->port, self::SSH_TIMEOUT);
            $ssh->setTimeout(self::SSH_TIMEOUT);

            if (! $ssh->login($server->ssh_username, $credential)) {
                return $this->result(
                    status: 'ssh_failed',
                    message: 'SSH authentication failed. Check stored credential.',
                    checkedAt: $checkedAt,
                    details: $this->emptyDetails(),
                );
            }
        } catch (Throwable) {
            return $this->result(
                status: 'ssh_failed',
                message: 'SSH authentication failed. Check stored credential.',
                checkedAt: $checkedAt,
                details: $this->emptyDetails(),
            );
        }

        // ── 3. Run sudo -n readiness commands ──────────────────────────
        $details = [];

        foreach (self::CHECKS as $key => $command) {
            try {
                $ssh->exec($command);
                $exitStatus = $ssh->getExitStatus();
                $details[$key] = ($exitStatus === 0);
            } catch (Throwable) {
                $details[$key] = false;
            }
        }

        $ssh->disconnect();

        // ── 4. Determine overall status ─────────────────────────────────
        $allPassed = ! in_array(false, $details, true);

        if ($allPassed) {
            return $this->result(
                status: 'ready',
                message: 'Target server is ready for JIT temporary credential provisioning.',
                checkedAt: $checkedAt,
                details: $details,
            );
        }

        return $this->result(
            status: 'not_ready',
            message: 'SSH login works, but sudo user management commands are not ready.',
            checkedAt: $checkedAt,
            details: $details,
        );
    }

    /**
     * Decrypt the server's stored SSH credential (password or private key).
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
     * @return array<string, bool>
     */
    private function emptyDetails(): array
    {
        return [
            'sudo_id_ok'  => false,
            'useradd_ok'  => false,
            'chpasswd_ok' => false,
            'usermod_ok'  => false,
            'userdel_ok'  => false,
        ];
    }

    /**
     * Build a structured readiness check result.
     *
     * @param  array<string, bool>  $details
     * @return array{status: string, message: string, checkedAt: \Illuminate\Support\Carbon, details: array<string, bool>}
     */
    private function result(
        string $status,
        string $message,
        \Illuminate\Support\Carbon $checkedAt,
        array $details,
    ): array {
        return compact('status', 'message', 'checkedAt', 'details');
    }
}
