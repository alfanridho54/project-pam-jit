<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">{{ __('Integrations') }}</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900">
                    {{ __('Proxmox') }}
                </h2>
            </div>

            <a href="{{ route('admin.proxmox.vms') }}" class="inline-flex items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">
                {{ __('View VMs') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('proxmox_result'))
                <div class="rounded-lg border {{ session('proxmox_result.ok') ? 'border-emerald-200 bg-emerald-50' : 'border-red-200 bg-red-50' }} p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-sm font-semibold {{ session('proxmox_result.ok') ? 'text-emerald-900' : 'text-red-900' }}">{{ __('Connection Test Result') }}</h3>
                            <p class="mt-1 text-sm {{ session('proxmox_result.ok') ? 'text-emerald-700' : 'text-red-700' }}">
                                {{ session('proxmox_result.message') }}
                            </p>
                        </div>
                        <x-badge :status="session('proxmox_result.ok') ? 'success' : 'failed'" />
                    </div>
                </div>
            @endif

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">{{ __('Connection Configuration') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Values are read from environment configuration. Token secret is never displayed.') }}</p>
                </div>

                <div class="p-6">
                    <dl class="grid gap-5 sm:grid-cols-2">
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Host') }}</dt>
                            <dd class="mt-1 font-mono text-sm text-gray-900">{{ $config['host'] ?: __('Not configured') }}</dd>
                        </div>

                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Port') }}</dt>
                            <dd class="mt-1 font-mono text-sm text-gray-900">{{ $config['port'] }}</dd>
                        </div>

                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Node') }}</dt>
                            <dd class="mt-1 font-mono text-sm text-gray-900">{{ $config['node'] ?: __('Not configured') }}</dd>
                        </div>

                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Token ID') }}</dt>
                            <dd class="mt-1 font-mono text-sm text-gray-900">{{ $config['token_id'] ?: __('Not configured') }}</dd>
                        </div>

                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Verify SSL') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <x-badge :status="$config['verify_ssl'] ? 'active' : 'inactive'" />
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-6 flex flex-wrap items-center justify-between gap-4 border-t border-gray-200 pt-6">
                        <p class="max-w-xl text-sm text-gray-500">{{ __('Use connection testing before importing VMs. If you update .env values, clear the config cache first.') }}</p>
                        <form method="POST" action="{{ route('admin.proxmox.test') }}">
                            @csrf

                            <x-primary-button>
                                {{ __('Test Connection') }}
                            </x-primary-button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                {{ __('Token secret is intentionally never displayed. Configure Proxmox values in .env and clear config cache after changes.') }}
            </div>
        </div>
    </div>
</x-app-layout>
