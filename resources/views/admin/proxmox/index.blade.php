<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Proxmox') }}
            </h2>

            <a href="{{ route('admin.proxmox.vms') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                {{ __('View VMs') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('proxmox_result'))
                <div class="rounded-md bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('Connection Test Result') }}</h3>
                    <p class="mt-2 text-sm {{ session('proxmox_result.ok') ? 'text-green-700' : 'text-red-700' }}">
                        {{ session('proxmox_result.message') }}
                    </p>
                </div>
            @endif

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900">{{ __('Configuration') }}</h3>

                <dl class="mt-6 grid gap-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Host') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $config['host'] ?: __('Not configured') }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Port') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $config['port'] }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Node') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $config['node'] ?: __('Not configured') }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Token ID') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $config['token_id'] ?: __('Not configured') }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Verify SSL') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $config['verify_ssl'] ? __('Yes') : __('No') }}</dd>
                    </div>
                </dl>

                <form method="POST" action="{{ route('admin.proxmox.test') }}" class="mt-6">
                    @csrf

                    <x-primary-button>
                        {{ __('Test Connection') }}
                    </x-primary-button>
                </form>
            </div>

            <div class="rounded-md bg-yellow-50 p-4 text-sm text-yellow-800">
                {{ __('Token secret is intentionally never displayed. Configure Proxmox values in .env and clear config cache after changes.') }}
            </div>
        </div>
    </div>
</x-app-layout>
