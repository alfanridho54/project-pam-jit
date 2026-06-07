<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TargetServer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TargetServerController extends Controller
{
    public function index(): View
    {
        $targetServers = TargetServer::query()
            ->latest()
            ->paginate(15);

        return view('admin.target-servers.index', compact('targetServers'));
    }

    public function create(): View
    {
        return view('admin.target-servers.create', [
            'targetServer' => new TargetServer([
                'port' => 22,
                'auth_type' => 'password',
                'is_active' => true,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTargetServer($request);

        $targetServer = TargetServer::create($this->attributesWithEncryptedSecrets($validated));

        return redirect()
            ->route('admin.target-servers.edit', $targetServer)
            ->with('success', 'Target server created.');
    }

    public function edit(TargetServer $targetServer): View
    {
        return view('admin.target-servers.edit', compact('targetServer'));
    }

    public function update(Request $request, TargetServer $targetServer): RedirectResponse
    {
        $validated = $this->validateTargetServer($request, $targetServer);

        $targetServer->update($this->attributesWithEncryptedSecrets($validated, $targetServer));

        return redirect()
            ->route('admin.target-servers.edit', $targetServer)
            ->with('success', 'Target server updated.');
    }

    public function destroy(TargetServer $targetServer): RedirectResponse
    {
        $targetServer->delete();

        return redirect()
            ->route('admin.target-servers.index')
            ->with('success', 'Target server deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateTargetServer(Request $request, ?TargetServer $targetServer = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'ssh_username' => ['nullable', 'string', 'max:255'],
            'auth_type' => ['required', Rule::in(['password', 'private_key'])],
            'ssh_password' => [$targetServer ? 'nullable' : 'required_if:auth_type,password', 'nullable', 'string'],
            'ssh_private_key' => [$targetServer ? 'nullable' : 'required_if:auth_type,private_key', 'nullable', 'string'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];

        $validated = $request->validate($rules);

        if ($targetServer) {
            $authType = $validated['auth_type'];

            if (
                $authType === 'password'
                && blank($validated['ssh_password'] ?? null)
                && ! $targetServer->hasPassword()
            ) {
                throw ValidationException::withMessages([
                    'ssh_password' => 'The SSH password field is required.',
                ]);
            }

            if (
                $authType === 'private_key'
                && blank($validated['ssh_private_key'] ?? null)
                && ! $targetServer->hasPrivateKey()
            ) {
                throw ValidationException::withMessages([
                    'ssh_private_key' => 'The SSH private key field is required.',
                ]);
            }
        }

        return $validated;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function attributesWithEncryptedSecrets(array $validated, ?TargetServer $targetServer = null): array
    {
        $attributes = [
            'name' => $validated['name'],
            'host' => $validated['host'],
            'port' => $validated['port'],
            'ssh_username' => $validated['ssh_username'] ?? null,
            'auth_type' => $validated['auth_type'],
            'description' => $validated['description'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ];

        if (filled($validated['ssh_password'] ?? null)) {
            $attributes['ssh_password_encrypted'] = Crypt::encryptString($validated['ssh_password']);
        } elseif (! $targetServer) {
            $attributes['ssh_password_encrypted'] = null;
        }

        if (filled($validated['ssh_private_key'] ?? null)) {
            $attributes['ssh_private_key_encrypted'] = Crypt::encryptString($validated['ssh_private_key']);
        } elseif (! $targetServer) {
            $attributes['ssh_private_key_encrypted'] = null;
        }

        return $attributes;
    }
}
