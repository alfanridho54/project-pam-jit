<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Integrations') }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                    {{ __('Discovered QEMU VMs') }}
                </h2>
            </div>

            <a href="{{ route('admin.proxmox.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-900 transition">
                {{ __('Back to Proxmox Config') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        @unless ($result['ok'])
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800 shadow-sm flex items-start">
                <svg class="h-5 w-5 text-red-500 mr-3 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <div>{{ $result['message'] }}</div>
            </div>
        @endunless

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <!-- Table Header and Search Filters -->
            <div class="border-b border-slate-100 p-5 bg-slate-50/20">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">{{ __('Proxmox Virtual Machines') }}</h3>
                        <p class="text-sm text-slate-500 mt-1">{{ __('Import hypervisor guest nodes into PAM. SSH connection profiles are configured individually.') }}</p>
                    </div>

                    <form method="GET" action="{{ route('admin.proxmox.vms') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <div>
                            <x-input-label for="q" :value="__('Filter VMs')" class="font-bold text-slate-700" />
                            <x-text-input id="q" name="q" type="search" class="mt-1 block w-full sm:w-72 border-slate-200 focus:border-indigo-500 focus:ring-indigo-500/20" :value="$q" placeholder="Name, ID, Node, IP..." />
                        </div>

                        <div>
                            <x-input-label for="status" :value="__('Status')" class="font-bold text-slate-700" />
                            <select id="status" name="status" class="mt-1 block w-full rounded-lg border-slate-200 bg-white text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/20 py-2 sm:w-36">
                                <option value="">{{ __('Any status') }}</option>
                                <option value="running" @selected($status === 'running')>{{ __('Running') }}</option>
                                <option value="stopped" @selected($status === 'stopped')>{{ __('Stopped') }}</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition focus:outline-none">
                                {{ __('Filter') }}
                            </button>

                            @if ($q !== '' || filled($status))
                                <a href="{{ route('admin.proxmox.vms') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition">
                                    {{ __('Reset') }}
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- VM listing Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('VMID') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('VM Name') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Power') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Hypervisor Node') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Resources') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Agent Discovered IP') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($vms as $vm)
                            <!-- Main VM details row -->
                            <tr class="bg-white border-t border-slate-100 hover:bg-slate-50/10">
                                <td class="px-6 py-4 text-sm font-bold text-slate-900 font-mono">{{ $vm['vmid'] }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-slate-900">{{ $vm['name'] }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <x-badge :status="$vm['status']" />
                                </td>
                                <td class="px-6 py-4 font-mono text-sm text-slate-600 font-semibold">{{ $vm['node'] }}</td>
                                <td class="px-6 py-4 text-sm text-slate-500 font-medium">
                                    {{ $vm['cpus'] ?? __('N/A') }} {{ __('CPUs') }} /
                                    {{ $vm['memory'] ? number_format($vm['memory'] / 1024 / 1024) . ' MB' : __('N/A') }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if ($vm['detected_ip'])
                                        <span class="font-mono font-semibold text-slate-800 bg-slate-100 px-2 py-0.5 border border-slate-200 rounded">{{ $vm['detected_ip'] }}</span>
                                    @else
                                        <span class="text-slate-400 italic text-xs">{{ __('No guest agent IP') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <!-- Dropdown import row -->
                            <tr class="bg-slate-50/40">
                                <td colspan="6" class="px-6 py-5 border-b border-slate-200">
                                    <div class="rounded-xl border border-slate-200 bg-white p-5 space-y-4 shadow-sm">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-3">
                                            <div>
                                                <h4 class="text-sm font-bold text-slate-900">{{ __('Provision VM as PAM Target') }}</h4>
                                                <p class="text-xs text-slate-500 mt-0.5">{{ __('Import VM parameters and configure default administrative credentials.') }}</p>
                                            </div>
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 {{ $vm['detected_ip'] ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-amber-50 text-amber-700 ring-amber-600/20' }}">
                                                <span class="mr-1.5 h-1.5 w-1.5 rounded-full {{ $vm['detected_ip'] ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                                {{ $vm['detected_ip'] ? __('Guest Agent Active') : __('Manually Configure Host') }}
                                            </span>
                                        </div>

                                        <form method="POST" action="{{ route('admin.proxmox.vms.import', $vm['vmid']) }}" class="grid gap-4 md:grid-cols-2 lg:grid-cols-6 items-end">
                                            @csrf
                                            <input type="hidden" name="name" value="{{ $vm['name'] }}">

                                            <div class="lg:col-span-2">
                                                <x-input-label :for="'host_'.$vm['vmid']" :value="__('Host Target')" class="font-bold text-slate-700 text-xs" />
                                                <x-text-input :id="'host_'.$vm['vmid']" name="host" type="text" class="mt-1 block w-full font-mono text-sm border-slate-200" :value="old('host', $vm['detected_ip'])" required />
                                                <x-input-error class="mt-2" :messages="$errors->get('host')" />
                                            </div>

                                            <div>
                                                <x-input-label :for="'ssh_username_'.$vm['vmid']" :value="__('SSH User')" class="font-bold text-slate-700 text-xs" />
                                                <x-text-input :id="'ssh_username_'.$vm['vmid']" name="ssh_username" type="text" class="mt-1 block w-full text-sm border-slate-200" :value="old('ssh_username')" required placeholder="e.g. root" />
                                                <x-input-error class="mt-2" :messages="$errors->get('ssh_username')" />
                                            </div>

                                            <div>
                                                <x-input-label :for="'auth_type_'.$vm['vmid']" :value="__('Auth Type')" class="font-bold text-slate-700 text-xs" />
                                                <select id="auth_type_{{ $vm['vmid'] }}" name="auth_type" class="mt-1 block w-full rounded-lg border-slate-200 bg-white text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/20 py-2 text-sm">
                                                    <option value="password" @selected(old('auth_type') === 'password')>{{ __('Password') }}</option>
                                                    <option value="private_key" @selected(old('auth_type') === 'private_key')>{{ __('Private key') }}</option>
                                                </select>
                                                <x-input-error class="mt-2" :messages="$errors->get('auth_type')" />
                                            </div>

                                            <div>
                                                <x-input-label :for="'ssh_password_'.$vm['vmid']" :value="__('SSH Password')" class="font-bold text-slate-700 text-xs" />
                                                <x-text-input :id="'ssh_password_'.$vm['vmid']" name="ssh_password" type="password" class="mt-1 block w-full text-sm border-slate-200" autocomplete="new-password" placeholder="••••••••" />
                                                <x-input-error class="mt-2" :messages="$errors->get('ssh_password')" />
                                            </div>

                                            <div>
                                                <x-input-label :for="'ssh_private_key_'.$vm['vmid']" :value="__('SSH Private Key')" class="font-bold text-slate-700 text-xs" />
                                                <textarea id="ssh_private_key_{{ $vm['vmid'] }}" name="ssh_private_key" rows="1" class="mt-1 block w-full rounded-lg border-slate-200 font-mono text-xxs shadow-sm focus:border-indigo-500 focus:ring-indigo-500/20 py-2.5" placeholder="Key data..."></textarea>
                                                <x-input-error class="mt-2" :messages="$errors->get('ssh_private_key')" />
                                            </div>

                                            <div class="lg:col-span-6 flex justify-end pt-2 border-t border-slate-100">
                                                <x-primary-button class="bg-indigo-600 hover:bg-indigo-500 px-4 py-2">
                                                    <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                                    </svg>
                                                    {{ __('Import Target Server') }}
                                                </x-primary-button>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-400">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5v9l9 5.25M3 7.5l9 5.25M9 9.75l-4.5 2.625m1.875-1.125L10.125 15m4.125-6.75L18.75 11.25M12 12.75v5.25" />
                                        </svg>
                                        @if ($q !== '' || filled($status))
                                            <span class="font-semibold text-slate-900">{{ __('No Proxmox VMs match this search.') }}</span>
                                            <span>{{ __('Try clearing filters.') }}</span>
                                        @else
                                            <span class="font-semibold text-slate-900">{{ __('No QEMU VMs found') }}</span>
                                            <span>{{ __('Check your Proxmox integration credentials, node config, and hypervisor statuses.') }}</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
