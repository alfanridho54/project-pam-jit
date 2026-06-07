<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Target Server') }}
            </h2>

            <a href="{{ route('admin.target-servers.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                {{ __('Back to Target Servers') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if (session('ssh_test_result'))
                <div class="mb-6 rounded-md bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('SSH Test Result') }}</h3>
                    <p class="mt-2 text-sm {{ session('ssh_test_result.ok') ? 'text-green-700' : 'text-red-700' }}">
                        {{ session('ssh_test_result.message') }}
                    </p>

                    @if (! empty(session('ssh_test_result.details.output')))
                        <pre class="mt-4 overflow-x-auto rounded bg-gray-900 p-4 text-sm text-gray-100">{{ session('ssh_test_result.details.output') }}</pre>
                    @endif
                </div>
            @endif

            <div class="mb-6 flex justify-end">
                <form method="POST" action="{{ route('admin.target-servers.test-connection', $targetServer) }}">
                    @csrf

                    <x-secondary-button>
                        {{ __('Test Connection') }}
                    </x-secondary-button>
                </form>
            </div>

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('admin.target-servers.update', $targetServer) }}" class="space-y-6">
                    @csrf
                    @method('PATCH')

                    @include('admin.target-servers.partials.form', [
                        'targetServer' => $targetServer,
                        'submitLabel' => __('Update Target Server'),
                    ])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
