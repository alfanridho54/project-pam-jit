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

        <!-- Health Check Result Panel -->
        @if (session('success') && ! session('ssh_test_result'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 text-emerald-800 p-4 shadow-sm flex items-center gap-3">
                <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('warning'))
            <div class="rounded-xl border border-amber-200 bg-amber-50 text-amber-800 p-4 shadow-sm flex items-center gap-3">
                <svg class="h-5 w-5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <p class="text-sm font-medium">{{ session('warning') }}</p>
            </div>
        @endif

        <!-- Health Status Panel -->
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between bg-slate-50/20">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">{{ __('Server Health Status') }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ __('Last recorded health check result for this server.') }}</p>
                </div>
                <form method="POST" action="{{ route('admin.target-servers.health-check', $targetServer) }}">
                    @csrf
                    <button type="submit" id="health-check-btn"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-teal-300 bg-teal-50 px-3 py-1.5 text-xs font-semibold text-teal-700 hover:bg-teal-100 transition focus:outline-none">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        {{ __('Run Health Check') }}
                    </button>
                </form>
            </div>
            <div class="px-6 py-5">
                @if ($targetServer->last_health_status)
                    <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Status') }}</dt>
                            <dd class="mt-1.5">
                                <x-badge :status="$targetServer->healthStatusBadgeVariant()">
                                    {{ $targetServer->healthStatusLabel() }}
                                </x-badge>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Last Checked') }}</dt>
                            <dd class="mt-1.5 text-sm font-mono text-slate-600">
                                {{ $targetServer->last_health_checked_at?->timezone('Asia/Jakarta')->format('Y-m-d H:i') ?? '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Latency') }}</dt>
                            <dd class="mt-1.5 text-sm font-mono text-slate-600">
                                {{ $targetServer->last_health_latency_ms !== null ? $targetServer->last_health_latency_ms . ' ms' : '—' }}
                            </dd>
                        </div>
                        <div class="sm:col-span-2 lg:col-span-1">
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Message') }}</dt>
                            <dd class="mt-1.5 text-sm text-slate-600 leading-relaxed">
                                {{ $targetServer->last_health_message ?? '—' }}
                            </dd>
                        </div>
                    </dl>
                @else
                    <p class="text-sm text-slate-400 italic">{{ __('No health check has been run yet. Click "Run Health Check" to begin.') }}</p>
                @endif
            </div>
        </div>

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


