<?php

namespace App\Services;

use App\Models\JitSession;
use App\Models\TargetServer;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Exception\UnableToConnectException;
use phpseclib3\Net\SSH2;
use Throwable;

class TemporaryLinuxCredentialService
{
    /**
     * @return array{ok: bool, username: ?string, password: ?string, message: string}
     */
    public function create(JitSession $session): array
    {
        if (! $this->enabled()) {
            return ['ok' => false, 'username' => null, 'password' => null, 'message' => 'Temporary credentials are disabled.'];
        }

        $session->loadMissing('targetServer');
        $username = $this->generateUsername($session);
        $password = $this->generatePassword();

        try {
            $ssh = $this->connectWithTargetCredential($session->targetServer);
            $this->ensureUserDoesNotExist($ssh, $username);

            $shell = $this->configPath('default_shell', '/bin/bash');
            $homeBase = rtrim($this->configPath('home_base', '/home'), '/');
            $home = $homeBase.'/'.$username;

            $this->run($ssh, [
                'sudo',
                'useradd',
                '-m',
                '-d',
                $home,
                '-s',
                $shell,
                $username,
            ], 'Create temporary Linux user failed.');

            $this->setPassword($ssh, $username, $password);

            return [
                'ok' => true,
                'username' => $username,
                'password' => $password,
                'message' => 'Temporary Linux credential created.',
            ];
        } catch (Throwable $exception) {
            return [
                'ok' => false,
                'username' => $username,
                'password' => null,
                'message' => $this->safeExceptionMessage($exception),
            ];
        }
    }

    /**
     * @return array{ok: bool, status: string, message: string}
     */
    public function cleanup(JitSession $session): array
    {
        if (! $session->uses_temporary_credential || blank($session->temporary_username)) {
            return ['ok' => true, 'status' => (string) $session->temporary_credential_status, 'message' => 'No temporary credential cleanup needed.'];
        }

        if (in_array($session->temporary_credential_status, [
            JitSession::TEMPORARY_CREDENTIAL_DISABLED,
            JitSession::TEMPORARY_CREDENTIAL_DELETED,
        ], true)) {
            return ['ok' => true, 'status' => $session->temporary_credential_status, 'message' => 'Temporary credential already cleaned up.'];
        }

        $mode = $this->cleanupMode();

        try {
            $session->loadMissing('targetServer');
            $ssh = $this->connectWithTargetCredential($session->targetServer);

            if (! $this->userExists($ssh, $session->temporary_username)) {
                $status = $mode === 'delete'
                    ? JitSession::TEMPORARY_CREDENTIAL_DELETED
                    : JitSession::TEMPORARY_CREDENTIAL_DISABLED;

                return ['ok' => true, 'status' => $status, 'message' => 'Temporary Linux user is already absent.'];
            }

            if ($mode === 'delete') {
                return $this->deleteUser($ssh, $session->temporary_username);
            }

            return $this->disableUser($ssh, $session->temporary_username);
        } catch (Throwable $exception) {
            $status = $mode === 'delete'
                ? JitSession::TEMPORARY_CREDENTIAL_DELETE_FAILED
                : JitSession::TEMPORARY_CREDENTIAL_DISABLE_FAILED;

            return ['ok' => false, 'status' => $status, 'message' => $this->safeExceptionMessage($exception)];
        }
    }

    public function encryptedPassword(string $plainPassword): string
    {
        return Crypt::encryptString($plainPassword);
    }

    public function decryptTemporaryPassword(JitSession $session): string
    {
        if (! $session->hasCreatedTemporaryCredential()) {
            throw new \RuntimeException('Temporary credential is not available for this session.');
        }

        return Crypt::decryptString($session->temporary_password_encrypted);
    }

    public function enabled(): bool
    {
        return (bool) config('services.temporary_credentials.enabled', true);
    }

    /**
     * @return array{ok: bool, status: string, message: string}
     */
    private function deleteUser(SSH2 $ssh, string $username): array
    {
        $result = $this->run($ssh, ['sudo', 'userdel', '-r', $username], 'Delete temporary Linux user failed.', false);

        if ($result['ok']) {
            return ['ok' => true, 'status' => JitSession::TEMPORARY_CREDENTIAL_DELETED, 'message' => 'Temporary Linux user deleted.'];
        }

        $disable = $this->disableUser($ssh, $username);

        return [
            'ok' => false,
            'status' => JitSession::TEMPORARY_CREDENTIAL_DELETE_FAILED,
            'message' => $disable['ok']
                ? 'Temporary Linux user was disabled, but deletion failed.'
                : $result['message'],
        ];
    }

    /**
     * @return array{ok: bool, status: string, message: string}
     */
    private function disableUser(SSH2 $ssh, string $username): array
    {
        $this->run($ssh, ['sudo', 'usermod', '-L', $username], 'Lock temporary Linux user failed.');

        $nologin = $this->remotePathExists($ssh, '/usr/sbin/nologin') ? '/usr/sbin/nologin' : '/sbin/nologin';
        $this->run($ssh, ['sudo', 'usermod', '-s', $nologin, $username], 'Disable temporary Linux user shell failed.');

        if ($this->commandExists($ssh, 'pkill')) {
            $this->run($ssh, ['sudo', 'pkill', '-KILL', '-u', $username], 'Terminate temporary user processes failed.', false, [0, 1]);
        }

        return ['ok' => true, 'status' => JitSession::TEMPORARY_CREDENTIAL_DISABLED, 'message' => 'Temporary Linux user disabled.'];
    }

    private function generateUsername(JitSession $session): string
    {
        $prefix = Str::of((string) config('services.temporary_credentials.username_prefix', 'jit'))
            ->lower()
            ->replaceMatches('/[^a-z0-9_]/', '_')
            ->trim('_')
            ->limit(12, '')
            ->value() ?: 'jit';

        $username = sprintf('%s_%d_%s', $prefix, $session->id, Str::lower(Str::random(4)));

        return Str::of($username)
            ->replaceMatches('/[^a-z0-9_]/', '_')
            ->limit(32, '')
            ->value();
    }

    private function generatePassword(): string
    {
        $length = max(16, (int) config('services.temporary_credentials.password_length', 24));
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%^&*_-+=';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $password;
    }

    private function cleanupMode(): string
    {
        $mode = (string) config('services.temporary_credentials.cleanup_mode', 'disable');

        return in_array($mode, ['disable', 'delete'], true) ? $mode : 'disable';
    }

    private function configPath(string $key, string $default): string
    {
        $value = (string) config("services.temporary_credentials.{$key}", $default);

        if (! str_starts_with($value, '/')) {
            return $default;
        }

        return $value;
    }

    private function connectWithTargetCredential(TargetServer $targetServer): SSH2
    {
        if (blank($targetServer->ssh_username)) {
            throw new \RuntimeException('Target server SSH username is missing.');
        }

        try {
            $credential = $this->targetCredential($targetServer);
            $ssh = new SSH2($targetServer->host, $targetServer->port, 10);
            $ssh->setTimeout(15);

            if (! $ssh->login($targetServer->ssh_username, $credential)) {
                throw new \RuntimeException('Target server SSH authentication failed.');
            }

            return $ssh;
        } catch (DecryptException) {
            throw new \RuntimeException('Stored target server SSH credential could not be decrypted.');
        } catch (UnableToConnectException) {
            throw new \RuntimeException('Unable to connect to target server over SSH.');
        }
    }

    private function targetCredential(TargetServer $targetServer): mixed
    {
        if ($targetServer->auth_type === 'password') {
            if (! $targetServer->hasPassword()) {
                throw new \RuntimeException('No SSH password is stored for this target server.');
            }

            return Crypt::decryptString($targetServer->ssh_password_encrypted);
        }

        if ($targetServer->auth_type === 'private_key') {
            if (! $targetServer->hasPrivateKey()) {
                throw new \RuntimeException('No SSH private key is stored for this target server.');
            }

            return PublicKeyLoader::loadPrivateKey(Crypt::decryptString($targetServer->ssh_private_key_encrypted));
        }

        throw new \RuntimeException('Unsupported target server SSH authentication type.');
    }

    private function ensureUserDoesNotExist(SSH2 $ssh, string $username): void
    {
        if ($this->userExists($ssh, $username)) {
            throw new \RuntimeException('Generated temporary username already exists on target server.');
        }
    }

    private function userExists(SSH2 $ssh, string $username): bool
    {
        $result = $this->run($ssh, ['id', '-u', $username], 'Check temporary Linux user failed.', false, [0, 1]);

        return $result['exit_code'] === 0;
    }

    private function remotePathExists(SSH2 $ssh, string $path): bool
    {
        $result = $this->run($ssh, ['test', '-x', $path], 'Check remote path failed.', false, [0, 1]);

        return $result['exit_code'] === 0;
    }

    private function commandExists(SSH2 $ssh, string $command): bool
    {
        $result = $this->run($ssh, ['command', '-v', $command], 'Check remote command failed.', false, [0, 1]);

        return $result['exit_code'] === 0;
    }

    private function setPassword(SSH2 $ssh, string $username, string $password): void
    {
        $payload = base64_encode($username.':'.$password);
        $command = 'printf %s '.$this->arg($payload).' | base64 -d | sudo chpasswd';
        $output = $ssh->exec($command);
        $exitCode = $ssh->getExitStatus();

        if ($exitCode !== null && $exitCode !== 0) {
            throw new \RuntimeException('Set temporary Linux user password failed.');
        }

        if ($output === false) {
            throw new \RuntimeException('Set temporary Linux user password failed.');
        }
    }

    /**
     * @param  array<int, string>  $command
     * @param  array<int, int>  $allowedExitCodes
     * @return array{ok: bool, exit_code: ?int, message: string, output: string}
     */
    private function run(
        SSH2 $ssh,
        array $command,
        string $failureMessage,
        bool $throw = true,
        array $allowedExitCodes = [0]
    ): array {
        $output = $ssh->exec($this->buildCommand($command));
        $exitCode = $ssh->getExitStatus();
        $ok = $output !== false && ($exitCode === null || in_array($exitCode, $allowedExitCodes, true));

        if (! $ok && $throw) {
            throw new \RuntimeException($failureMessage);
        }

        return [
            'ok' => $ok,
            'exit_code' => $exitCode,
            'message' => $ok ? 'Command completed.' : $failureMessage,
            'output' => $output === false ? '' : trim((string) $output),
        ];
    }

    /**
     * @param  array<int, string>  $parts
     */
    private function buildCommand(array $parts): string
    {
        return implode(' ', array_map(fn (string $part): string => $this->arg($part), $parts));
    }

    private function arg(string $value): string
    {
        return escapeshellarg($value);
    }

    private function safeExceptionMessage(Throwable $exception): string
    {
        $message = $exception->getMessage();

        return Str::limit($message !== '' ? $message : 'Temporary credential operation failed.', 500, '');
    }
}
