<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Infrastructure') }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                    {{ __('Edit Target Server') }}
                </h2>
            </div>

            <a href="{{ route('admin.target-servers.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-900 transition">
                {{ __('Back to Target Servers') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        <!-- Connection Test Panel -->
        @if (session('ssh_test_result'))
            <div class="rounded-xl border {{ session('ssh_test_result.ok') ? 'border-emerald-200 bg-emerald-50/50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-800' }} p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex">
                        @if(session('ssh_test_result.ok'))
                            <svg class="h-5 w-5 text-emerald-600 mr-3 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        @else
                            <svg class="h-5 w-5 text-red-600 mr-3 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                            </svg>
                        @endif
                        <div>
                            <h3 class="text-sm font-bold">{{ __('SSH Connection Test') }}</h3>
                            <p class="mt-1 text-sm font-medium">
                                {{ session('ssh_test_result.message') }}
                            </p>
                        </div>
                    </div>
                    <x-badge :status="session('ssh_test_result.ok') ? 'success' : 'failed'" />
                </div>

                @if (! empty(session('ssh_test_result.details.output')))
                    <div class="mt-4 rounded-lg overflow-hidden border border-slate-950 bg-[#0b0f19]">
                        <div class="bg-slate-900 px-4 py-2 border-b border-slate-950/80 flex items-center justify-between">
                            <span class="text-xxs font-mono text-slate-500 select-none">{{ __('test-connection-stderr') }}</span>
                        </div>
                        <pre class="p-4 overflow-x-auto text-xs font-mono text-slate-100 whitespace-pre-wrap">{{ session('ssh_test_result.details.output') }}</pre>
                    </div>
                @endif
            </div>
        @endif

        <!-- Quick Test Card -->
        <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div>
                <h3 class="text-sm font-bold text-slate-900">{{ __('Verify Server Connectivity') }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">{{ __('Test SSH communication configuration using stored encrypted keys.') }}</p>
            </div>
            <form method="POST" action="{{ route('admin.target-servers.test-connection', $targetServer) }}">
                @csrf
                <x-secondary-button type="submit" class="border-slate-200">
                    {{ __('Test Connection') }}
                </x-secondary-button>
            </form>
        </div>

        <!-- Update Form Container -->
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-5 bg-slate-50/20">
                <h3 class="text-base font-bold text-slate-900">{{ $targetServer->name }}</h3>
                <p class="text-sm text-slate-500 mt-1">{{ __('Update target parameters or reset system access credentials.') }}</p>
            </div>

            <form method="POST" action="{{ route('admin.target-servers.update', $targetServer) }}" class="space-y-6">
                @csrf
                @method('PATCH')
                <div class="p-6 sm:p-8">
                    @include('admin.target-servers.partials.form', [
                        'targetServer' => $targetServer,
                        'submitLabel' => __('Update Target Server'),
                    ])
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
