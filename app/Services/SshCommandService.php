<?php

namespace App\Services;

use App\Models\JitSession;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Exception\UnableToConnectException;
use phpseclib3\Net\SSH2;
use Throwable;

class SshCommandService
{
    /**
     * @return array{ok: bool, status: string, message: string, output: ?string, exit_code: ?int}
     */
    public function executeCommand(JitSession $session, string $command): array
    {
        $user = request()->user();
        $session->loadMissing('targetServer');
        $targetServer = $session->targetServer;

        if (! $user || $session->user_id !== $user->id) {
            return $this->result(false, 'denied', 'You are not allowed to use this JIT session.');
        }

        if (! $session->isUsable()) {
            return $this->result(false, 'denied', 'This JIT session is not active or has expired.');
        }

        if (! $targetServer->is_active) {
            return $this->result(false, 'denied', 'The target server is inactive.');
        }

        if (! $session->hasCreatedTemporaryCredential() && blank($targetServer->ssh_username)) {
            return $this->result(false, 'failed', 'SSH username is missing.');
        }

        try {
            $login = $this->loginFor($session);
        } catch (DecryptException) {
            return $this->result(false, 'failed', 'Stored SSH credential could not be decrypted.');
        } catch (Throwable $exception) {
            return $this->result(false, 'failed', $exception->getMessage());
        }

        try {
            $ssh = new SSH2($targetServer->host, $targetServer->port, 10);
            $ssh->setTimeout(10);

            if (! $ssh->login($login['username'], $login['credential'])) {
                return $this->result(false, 'failed', 'SSH authentication failed.');
            }

            $output = $ssh->exec($command);
            $exitCode = $ssh->getExitStatus();
            $output = $output === false ? '' : trim($output ?? '');

            if ($exitCode !== null && $exitCode !== 0) {
                return $this->result(false, 'failed', 'Command executed with a non-zero exit code.', $output, $exitCode);
            }

            return $this->result(true, 'success', 'Command executed successfully.', $output, $exitCode);
        } catch (UnableToConnectException $exception) {
            return $this->result(false, 'failed', 'Unable to connect to SSH server: '.$exception->getMessage());
        } catch (Throwable $exception) {
            return $this->result(false, 'failed', 'SSH command failed: '.$exception->getMessage());
        }
    }

    /**
     * @return array{username: string, credential: mixed}
     */
    private function loginFor(JitSession $session): array
    {
        $targetServer = $session->targetServer;

        if ($session->hasCreatedTemporaryCredential()) {
            return [
                'username' => $session->temporary_username,
                'credential' => Crypt::decryptString($session->temporary_password_encrypted),
            ];
        }

        if ($targetServer->auth_type === 'password') {
            if (! $targetServer->hasPassword()) {
                throw new \RuntimeException('No SSH password is stored for this target server.');
            }

            return [
                'username' => $targetServer->ssh_username,
                'credential' => Crypt::decryptString($targetServer->ssh_password_encrypted),
            ];
        }

        if ($targetServer->auth_type === 'private_key') {
            if (! $targetServer->hasPrivateKey()) {
                throw new \RuntimeException('No SSH private key is stored for this target server.');
            }

            return [
                'username' => $targetServer->ssh_username,
                'credential' => PublicKeyLoader::loadPrivateKey(Crypt::decryptString($targetServer->ssh_private_key_encrypted)),
            ];
        }

        throw new \RuntimeException('Unsupported SSH authentication type.');
    }

    /**
     * @return array{ok: bool, status: string, message: string, output: ?string, exit_code: ?int}
     */
    private function result(
        bool $ok,
        string $status,
        string $message,
        ?string $output = null,
        ?int $exitCode = null
    ): array {
        return [
            'ok' => $ok,
            'status' => $status,
            'message' => $message,
            'output' => $output,
            'exit_code' => $exitCode,
        ];
    }
}
