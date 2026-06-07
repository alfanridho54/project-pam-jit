<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Proxmox VMs') }}
            </h2>

            <a href="{{ route('admin.proxmox.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                {{ __('Back to Proxmox') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @unless ($result['ok'])
                <div class="mb-6 rounded-md bg-red-50 p-4 text-sm text-red-700">
                    {{ $result['message'] }}
                </div>
            @endunless

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('VMID') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Name') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Node') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('CPU / Memory') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Detected IP') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($vms as $vm)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ $vm['vmid'] }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ $vm['name'] }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ ucfirst($vm['status']) }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ $vm['node'] }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                        {{ $vm['cpus'] ?? __('N/A') }} CPU /
                                        {{ $vm['memory'] ? number_format($vm['memory'] / 1024 / 1024) . ' MB' : __('N/A') }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                        {{ $vm['detected_ip'] ?? __('Not detected') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="bg-gray-50 px-6 py-4">
                                        <form method="POST" action="{{ route('admin.proxmox.vms.import', $vm['vmid']) }}" class="grid gap-4 lg:grid-cols-6">
                                            @csrf

                                            <input type="hidden" name="name" value="{{ $vm['name'] }}">

                                            <div class="lg:col-span-2">
                                                <x-input-label :for="'host_'.$vm['vmid']" :value="__('Host')" />
                                                <x-text-input :id="'host_'.$vm['vmid']" name="host" type="text" class="mt-1 block w-full" :value="old('host', $vm['detected_ip'])" required />
                                                <x-input-error class="mt-2" :messages="$errors->get('host')" />
                                            </div>

                                            <div>
                                                <x-input-label :for="'ssh_username_'.$vm['vmid']" :value="__('SSH Username')" />
                                                <x-text-input :id="'ssh_username_'.$vm['vmid']" name="ssh_username" type="text" class="mt-1 block w-full" :value="old('ssh_username')" required />
                                                <x-input-error class="mt-2" :messages="$errors->get('ssh_username')" />
                                            </div>

                                            <div>
                                                <x-input-label :for="'auth_type_'.$vm['vmid']" :value="__('Auth Type')" />
                                                <select id="auth_type_{{ $vm['vmid'] }}" name="auth_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                    <option value="password" @selected(old('auth_type') === 'password')>{{ __('Password') }}</option>
                                                    <option value="private_key" @selected(old('auth_type') === 'private_key')>{{ __('Private key') }}</option>
                                                </select>
                                                <x-input-error class="mt-2" :messages="$errors->get('auth_type')" />
                                            </div>

                                            <div>
                                                <x-input-label :for="'ssh_password_'.$vm['vmid']" :value="__('SSH Password')" />
                                                <x-text-input :id="'ssh_password_'.$vm['vmid']" name="ssh_password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                                                <x-input-error class="mt-2" :messages="$errors->get('ssh_password')" />
                                            </div>

                                            <div>
                                                <x-input-label :for="'ssh_private_key_'.$vm['vmid']" :value="__('Private Key')" />
                                                <textarea id="ssh_private_key_{{ $vm['vmid'] }}" name="ssh_private_key" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                                <x-input-error class="mt-2" :messages="$errors->get('ssh_private_key')" />
                                            </div>

                                            <div class="flex items-end">
                                                <x-primary-button>
                                                    {{ __('Import') }}
                                                </x-primary-button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                                        {{ __('No Proxmox QEMU VMs found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
