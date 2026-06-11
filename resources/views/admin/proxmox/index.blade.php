<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Integrations') }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                    {{ __('Proxmox VE Control') }}
                </h2>
            </div>

            <a href="{{ route('admin.proxmox.vms') }}" class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-400 transition">
                <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3m3 3a3 3 0 1 0 6 0m-6 0H3m16.5 0a3 3 0 0 1-3 3m3-3a3 3 0 1 0-6 0m6 0h1.5" />
                </svg>
                {{ __('Virtual Machines') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        <!-- Proxmox test connection status alert -->
        @if (session('proxmox_result'))
            <div class="rounded-xl border {{ session('proxmox_result.ok') ? 'border-emerald-200 bg-emerald-50/50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-800' }} p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex">
                        @if(session('proxmox_result.ok'))
                            <svg class="h-5 w-5 text-emerald-600 mr-3 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        @else
                            <svg class="h-5 w-5 text-red-600 mr-3 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                            </svg>
                        @endif
                        <div>
                            <h3 class="text-sm font-bold">{{ __('Proxmox Connection Test') }}</h3>
                            <p class="mt-1 text-sm font-medium">
                                {{ session('proxmox_result.message') }}
                            </p>
                        </div>
                    </div>
                    <x-badge :status="session('proxmox_result.ok') ? 'success' : 'failed'" />
                </div>
            </div>
        @endif

        <!-- Configuration Card -->
        <div class="max-w-4xl mx-auto space-y-6">
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5 bg-slate-50/20">
                    <h3 class="text-base font-bold text-slate-900">{{ __('API Connection Configuration') }}</h3>
                    <p class="text-sm text-slate-500 mt-1">{{ __('Parameters are loaded from your secure environment variables (.env). API tokens remain hidden.') }}</p>
                </div>

                <div class="p-6 sm:p-8 space-y-6">
                    <dl class="grid gap-5 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-150 bg-slate-50/50 p-4">
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Host IP / Endpoint') }}</dt>
                            <dd class="mt-1.5 font-mono text-sm text-slate-800 font-semibold">{{ $config['host'] ?: __('Not configured') }}</dd>
                        </div>

                        <div class="rounded-xl border border-slate-150 bg-slate-50/50 p-4">
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Port') }}</dt>
                            <dd class="mt-1.5 font-mono text-sm text-slate-800 font-semibold">{{ $config['port'] }}</dd>
                        </div>

                        <div class="rounded-xl border border-slate-150 bg-slate-50/50 p-4">
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Default Node') }}</dt>
                            <dd class="mt-1.5 font-mono text-sm text-slate-800 font-semibold">{{ $config['node'] ?: __('Not configured') }}</dd>
                        </div>

                        <div class="rounded-xl border border-slate-150 bg-slate-50/50 p-4">
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Token Identifier') }}</dt>
                            <dd class="mt-1.5 font-mono text-sm text-slate-800 font-semibold truncate">{{ $config['token_id'] ?: __('Not configured') }}</dd>
                        </div>

                        <div class="rounded-xl border border-slate-150 bg-slate-50/50 p-4 sm:col-span-2">
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">{{ __('Verify SSL Certificates') }}</dt>
                            <dd class="text-sm">
                                <x-badge :status="$config['verify_ssl'] ? 'active' : 'inactive'" />
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-t border-slate-100 pt-6">
                        <p class="text-xs text-slate-400 max-w-lg leading-relaxed">{{ __('Always run connectivity checks after configuration updates. Be sure to run config:clear to load updated .env details.') }}</p>
                        <form method="POST" action="{{ route('admin.proxmox.test') }}">
                            @csrf
                            <x-primary-button class="bg-indigo-600 hover:bg-indigo-500">
                                {{ __('Test API Connectivity') }}
                            </x-primary-button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Security notice -->
            <p class="text-center text-xs text-slate-400">
                <svg class="inline h-3.5 w-3.5 mr-1 align-middle text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                {{ __('Token secrets are read into memory only during active API requests and are never stored to disk, database, or logs.') }}
            </p>
        </div>
    </div>
</x-app-layout>
