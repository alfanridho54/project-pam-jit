<?php

namespace App\Services;

use App\Models\TargetServer;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Exception\UnableToConnectException;
use phpseclib3\Net\SSH2;
use Throwable;

class SshConnectionService
{
    /**
     * @return array{ok: bool, message: string, details: array<string, mixed>}
     */
    public function testConnection(TargetServer $targetServer): array
    {
        if (blank($targetServer->ssh_username)) {
            return $this->failure('SSH username is missing.');
        }

        try {
            $credential = $this->credentialFor($targetServer);
        } catch (DecryptException) {
            return $this->failure('Stored SSH credential could not be decrypted.');
        } catch (Throwable $exception) {
            return $this->failure($exception->getMessage());
        }

        try {
            $ssh = new SSH2($targetServer->host, $targetServer->port, 10);
            $ssh->setTimeout(15);

            if (! $ssh->login($targetServer->ssh_username, $credential)) {
                return $this->failure('SSH authentication failed.', [
                    'host' => $targetServer->host,
                    'port' => $targetServer->port,
                    'auth_type' => $targetServer->auth_type,
                ]);
            }

            $output = trim($ssh->exec('whoami && hostname && uptime') ?? '');

            if ($ssh->getExitStatus() !== 0 && $ssh->getExitStatus() !== null) {
                return $this->failure('SSH command executed but returned a non-zero exit status.', [
                    'exit_status' => $ssh->getExitStatus(),
                    'output' => $output,
                ]);
            }

            return [
                'ok' => true,
                'message' => 'SSH connection succeeded.',
                'details' => [
                    'host' => $targetServer->host,
                    'port' => $targetServer->port,
                    'auth_type' => $targetServer->auth_type,
                    'output' => $output,
                ],
            ];
        } catch (UnableToConnectException $exception) {
            return $this->failure('Unable to connect to SSH server.', [
                'error' => $exception->getMessage(),
                'host' => $targetServer->host,
                'port' => $targetServer->port,
            ]);
        } catch (Throwable $exception) {
            return $this->failure('SSH test failed: '.$exception->getMessage(), [
                'host' => $targetServer->host,
                'port' => $targetServer->port,
            ]);
        }
    }

    private function credentialFor(TargetServer $targetServer): mixed
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

        throw new \RuntimeException('Unsupported SSH authentication type.');
    }

    /**
     * @param  array<string, mixed>  $details
     * @return array{ok: false, message: string, details: array<string, mixed>}
     */
    private function failure(string $message, array $details = []): array
    {
        return [
            'ok' => false,
            'message' => $message,
            'details' => $details,
        ];
    }
}
