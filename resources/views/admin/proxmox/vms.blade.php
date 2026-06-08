<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">{{ __('Proxmox') }}</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900">
                    {{ __('QEMU VMs') }}
                </h2>
            </div>

            <a href="{{ route('admin.proxmox.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                {{ __('Back to Proxmox') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @unless ($result['ok'])
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 shadow-sm">
                    {{ $result['message'] }}
                </div>
            @endunless

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-5 py-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">{{ __('Discovered Virtual Machines') }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ __('Import a VM as a Target Server by providing SSH credentials. Secrets are encrypted and never displayed again.') }}</p>
                        </div>

                        <form method="GET" action="{{ route('admin.proxmox.vms') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                            <div>
                                <x-input-label for="q" :value="__('Search')" />
                                <x-text-input id="q" name="q" type="search" class="mt-1 block w-full sm:w-72" :value="$q" placeholder="Search by VMID, name, IP, or node" />
                            </div>

                            <div>
                                <x-input-label for="status" :value="__('Status')" />
                                <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-36">
                                    <option value="">{{ __('Any status') }}</option>
                                    <option value="running" @selected($status === 'running')>{{ __('Running') }}</option>
                                    <option value="stopped" @selected($status === 'stopped')>{{ __('Stopped') }}</option>
                                </select>
                            </div>

                            <div class="flex items-center gap-3">
                                <button type="submit" class="inline-flex items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">
                                    {{ __('Search') }}
                                </button>

                                @if ($q !== '' || filled($status))
                                    <a href="{{ route('admin.proxmox.vms') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                                        {{ __('Reset') }}
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('VMID') }}</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Name') }}</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Status') }}</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Node') }}</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('CPU / Memory') }}</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Detected IP') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($vms as $vm)
                                <tr class="hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-gray-900">{{ $vm['vmid'] }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-gray-900">{{ $vm['name'] }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm">
                                        <x-badge :status="$vm['status']" />
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 font-mono text-sm text-gray-600">{{ $vm['node'] }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">
                                        {{ $vm['cpus'] ?? __('N/A') }} CPU /
                                        {{ $vm['memory'] ? number_format($vm['memory'] / 1024 / 1024) . ' MB' : __('N/A') }}
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">
                                        @if ($vm['detected_ip'])
                                            <span class="font-mono">{{ $vm['detected_ip'] }}</span>
                                        @else
                                            <span class="text-gray-400">{{ __('Not detected') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="bg-gray-50 px-5 py-5">
                                        <div class="rounded-lg border border-gray-200 bg-white p-4">
                                            <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                                <div>
                                                    <h4 class="text-sm font-semibold text-gray-900">{{ __('Import VM as Target Server') }}</h4>
                                                    <p class="text-sm text-gray-500">{{ __('Provide SSH access details for the imported target server.') }}</p>
                                                </div>
                                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 {{ $vm['detected_ip'] ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-amber-50 text-amber-700 ring-amber-600/20' }}">
                                                    {{ $vm['detected_ip'] ? __('IP detected') : __('Host required') }}
                                                </span>
                                            </div>

                                            <form method="POST" action="{{ route('admin.proxmox.vms.import', $vm['vmid']) }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
                                                @csrf

                                                <input type="hidden" name="name" value="{{ $vm['name'] }}">

                                                <div class="xl:col-span-2">
                                                    <x-input-label :for="'host_'.$vm['vmid']" :value="__('Host')" />
                                                    <x-text-input :id="'host_'.$vm['vmid']" name="host" type="text" class="mt-1 block w-full font-mono" :value="old('host', $vm['detected_ip'])" required />
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

                                                <div class="flex items-end xl:col-start-6">
                                                    <x-primary-button>
                                                        {{ __('Import VM') }}
                                                    </x-primary-button>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-12 text-center">
                                        @if ($q !== '' || filled($status))
                                            <div class="text-sm font-medium text-gray-900">{{ __('No Proxmox VMs match this search.') }}</div>
                                            <div class="mt-1 text-sm text-gray-500">{{ __('Try a different search term or clear the filters.') }}</div>
                                        @else
                                            <div class="text-sm font-medium text-gray-900">{{ __('No QEMU VMs found') }}</div>
                                            <div class="mt-1 text-sm text-gray-500">{{ __('Check Proxmox configuration, node name, token permissions, and guest availability.') }}</div>
                                        @endif
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
