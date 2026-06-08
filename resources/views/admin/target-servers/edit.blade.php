<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">{{ __('Target Servers') }}</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900">
                    {{ __('Edit Target Server') }}
                </h2>
            </div>

            <a href="{{ route('admin.target-servers.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                {{ __('Back to Target Servers') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('ssh_test_result'))
                <div class="mb-6 rounded-lg border {{ session('ssh_test_result.ok') ? 'border-emerald-200 bg-emerald-50' : 'border-red-200 bg-red-50' }} p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-sm font-semibold {{ session('ssh_test_result.ok') ? 'text-emerald-900' : 'text-red-900' }}">{{ __('SSH Test Result') }}</h3>
                            <p class="mt-1 text-sm {{ session('ssh_test_result.ok') ? 'text-emerald-700' : 'text-red-700' }}">
                                {{ session('ssh_test_result.message') }}
                            </p>
                        </div>
                        <x-badge :status="session('ssh_test_result.ok') ? 'success' : 'failed'" />
                    </div>

                    @if (! empty(session('ssh_test_result.details.output')))
                        <pre class="mt-4 overflow-x-auto rounded-md bg-gray-950 p-4 text-sm text-gray-100">{{ session('ssh_test_result.details.output') }}</pre>
                    @endif
                </div>
            @endif

            <div class="mb-6 flex justify-end rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <form method="POST" action="{{ route('admin.target-servers.test-connection', $targetServer) }}">
                    @csrf

                    <x-secondary-button>
                        {{ __('Test Connection') }}
                    </x-secondary-button>
                </form>
            </div>

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">{{ $targetServer->name }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Leave secret fields blank to keep stored encrypted credentials.') }}</p>
                </div>
                <form method="POST" action="{{ route('admin.target-servers.update', $targetServer) }}" class="space-y-6">
                    @csrf
                    @method('PATCH')
                    <div class="p-6">
                        @include('admin.target-servers.partials.form', [
                            'targetServer' => $targetServer,
                            'submitLabel' => __('Update Target Server'),
                        ])
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
