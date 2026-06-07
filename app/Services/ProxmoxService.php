<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Throwable;

class ProxmoxService
{
    /**
     * @return array{ok: bool, message: string, details: array<string, mixed>}
     */
    public function testConnection(): array
    {
        $missing = $this->missingConfig();

        if ($missing !== []) {
            return $this->failure('Proxmox configuration is incomplete.', ['missing' => $missing]);
        }

        try {
            $response = $this->client()->get('/version');

            if (! $response->successful()) {
                return $this->failure('Proxmox connection test failed.', [
                    'status' => $response->status(),
                    'body' => $response->json('errors') ?? $response->json('message'),
                ]);
            }

            return [
                'ok' => true,
                'message' => 'Proxmox connection succeeded.',
                'details' => [
                    'host' => config('services.proxmox.host'),
                    'node' => config('services.proxmox.node'),
                    'version' => $response->json('data.version'),
                ],
            ];
        } catch (Throwable $exception) {
            return $this->failure('Proxmox connection test failed: '.$exception->getMessage());
        }
    }

    /**
     * @return array{ok: bool, message: string, vms: array<int, array<string, mixed>>, details: array<string, mixed>}
     */
    public function listQemuVms(): array
    {
        $missing = $this->missingConfig();

        if ($missing !== []) {
            return [
                'ok' => false,
                'message' => 'Proxmox configuration is incomplete.',
                'vms' => [],
                'details' => ['missing' => $missing],
            ];
        }

        try {
            $node = config('services.proxmox.node');
            $response = $this->client()->get("/nodes/{$node}/qemu");

            if (! $response->successful()) {
                return [
                    'ok' => false,
                    'message' => 'Unable to list Proxmox QEMU VMs.',
                    'vms' => [],
                    'details' => ['status' => $response->status()],
                ];
            }

            $vms = collect($response->json('data', []))
                ->map(function (array $vm) use ($node): array {
                    $interfaces = $this->getVmAgentInterfaces((int) $vm['vmid']);

                    return [
                        'vmid' => (int) $vm['vmid'],
                        'name' => $vm['name'] ?? "VM {$vm['vmid']}",
                        'status' => $vm['status'] ?? 'unknown',
                        'node' => $node,
                        'cpus' => $vm['cpus'] ?? null,
                        'memory' => $vm['maxmem'] ?? null,
                        'detected_ip' => $this->detectIp($interfaces['interfaces'] ?? []),
                        'agent_available' => $interfaces['ok'],
                    ];
                })
                ->sortBy('vmid')
                ->values()
                ->all();

            return [
                'ok' => true,
                'message' => 'Proxmox QEMU VMs loaded.',
                'vms' => $vms,
                'details' => ['node' => $node],
            ];
        } catch (Throwable $exception) {
            return [
                'ok' => false,
                'message' => 'Unable to list Proxmox QEMU VMs: '.$exception->getMessage(),
                'vms' => [],
                'details' => [],
            ];
        }
    }

    /**
     * @return array{ok: bool, message: string, interfaces: array<int, array<string, mixed>>}
     */
    public function getVmAgentInterfaces(int $vmid): array
    {
        try {
            $node = config('services.proxmox.node');
            $response = $this->client()->get("/nodes/{$node}/qemu/{$vmid}/agent/network-get-interfaces");

            if (! $response->successful()) {
                return [
                    'ok' => false,
                    'message' => 'Guest agent network interfaces are unavailable.',
                    'interfaces' => [],
                ];
            }

            return [
                'ok' => true,
                'message' => 'Guest agent network interfaces loaded.',
                'interfaces' => $response->json('data.result', []),
            ];
        } catch (Throwable) {
            return [
                'ok' => false,
                'message' => 'Guest agent network interfaces are unavailable.',
                'interfaces' => [],
            ];
        }
    }

    private function client(): PendingRequest
    {
        $host = rtrim((string) config('services.proxmox.host'), '/');
        $port = config('services.proxmox.port');
        $tokenId = config('services.proxmox.token_id');
        $tokenSecret = config('services.proxmox.token_secret');

        return Http::baseUrl("https://{$host}:{$port}/api2/json")
            ->withHeaders([
                'Authorization' => "PVEAPIToken={$tokenId}={$tokenSecret}",
            ])
            ->acceptJson()
            ->timeout(15)
            ->withOptions([
                'verify' => (bool) config('services.proxmox.verify_ssl'),
            ]);
    }

    /**
     * @return array<int, string>
     */
    private function missingConfig(): array
    {
        return collect([
            'PROXMOX_HOST' => config('services.proxmox.host'),
            'PROXMOX_NODE' => config('services.proxmox.node'),
            'PROXMOX_TOKEN_ID' => config('services.proxmox.token_id'),
            'PROXMOX_TOKEN_SECRET' => config('services.proxmox.token_secret'),
        ])
            ->filter(fn ($value): bool => blank($value))
            ->keys()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $interfaces
     */
    private function detectIp(array $interfaces): ?string
    {
        foreach ($interfaces as $interface) {
            foreach ($interface['ip-addresses'] ?? [] as $address) {
                $ip = $address['ip-address'] ?? null;
                $type = $address['ip-address-type'] ?? null;

                if ($type === 'ipv4' && $ip && ! str_starts_with($ip, '127.')) {
                    return $ip;
                }
            }
        }

        return null;
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
