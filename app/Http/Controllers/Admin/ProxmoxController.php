<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TargetServer;
use App\Services\AuditLogService;
use App\Services\ProxmoxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProxmoxController extends Controller
{
    public function index(): View
    {
        return view('admin.proxmox.index', [
            'config' => [
                'host' => config('services.proxmox.host'),
                'port' => config('services.proxmox.port'),
                'node' => config('services.proxmox.node'),
                'token_id' => config('services.proxmox.token_id'),
                'verify_ssl' => config('services.proxmox.verify_ssl'),
            ],
        ]);
    }

    public function test(Request $request, ProxmoxService $proxmox, AuditLogService $auditLog): RedirectResponse
    {
        $result = $proxmox->testConnection();

        $auditLog->log(
            $request->user(),
            $result['ok'] ? 'proxmox_connection_test_succeeded' : 'proxmox_connection_test_failed',
            null,
            $result['message'],
            [
                'host' => config('services.proxmox.host'),
                'node' => config('services.proxmox.node'),
                'ok' => $result['ok'],
            ]
        );

        return back()
            ->with($result['ok'] ? 'success' : 'error', $result['message'])
            ->with('proxmox_result', $result);
    }

    public function vms(ProxmoxService $proxmox): View
    {
        $result = $proxmox->listQemuVms();

        return view('admin.proxmox.vms', [
            'result' => $result,
            'vms' => $result['vms'],
        ]);
    }

    public function import(Request $request, int $vmid, ProxmoxService $proxmox, AuditLogService $auditLog): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'host' => ['required', 'string', 'max:255'],
            'ssh_username' => ['required', 'string', 'max:255'],
            'auth_type' => ['required', Rule::in(['password', 'private_key'])],
            'ssh_password' => ['required_if:auth_type,password', 'nullable', 'string'],
            'ssh_private_key' => ['required_if:auth_type,private_key', 'nullable', 'string'],
        ]);

        $vms = collect($proxmox->listQemuVms()['vms'] ?? []);
        $vm = $vms->firstWhere('vmid', $vmid);

        if (! $vm) {
            $auditLog->log(
                $request->user(),
                'proxmox_vm_import_failed',
                null,
                "Proxmox VM {$vmid} import failed: VM not found.",
                ['vmid' => $vmid]
            );

            return back()->withInput()->with('error', 'Unable to import VM because it was not found in Proxmox.');
        }

        $targetServer = TargetServer::create([
            'name' => $validated['name'],
            'host' => $validated['host'],
            'port' => 22,
            'ssh_username' => $validated['ssh_username'],
            'auth_type' => $validated['auth_type'],
            'ssh_password_encrypted' => filled($validated['ssh_password'] ?? null)
                ? Crypt::encryptString($validated['ssh_password'])
                : null,
            'ssh_private_key_encrypted' => filled($validated['ssh_private_key'] ?? null)
                ? Crypt::encryptString($validated['ssh_private_key'])
                : null,
            'description' => "Imported from Proxmox VM {$vmid} on node {$vm['node']}.",
            'is_active' => true,
        ]);

        $auditLog->log(
            $request->user(),
            'proxmox_vm_imported_as_target_server',
            $targetServer,
            "Proxmox VM {$vmid} imported as target server {$targetServer->name}.",
            [
                'vmid' => $vmid,
                'node' => $vm['node'],
                'target_server_id' => $targetServer->id,
                'host' => $targetServer->host,
                'auth_type' => $targetServer->auth_type,
            ]
        );

        return redirect()
            ->route('admin.target-servers.edit', $targetServer)
            ->with('success', 'Proxmox VM imported as a target server.');
    }
}
