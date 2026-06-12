<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Infrastructure') }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                    {{ __('Target Servers') }}
                </h2>
            </div>

            <a href="{{ route('admin.target-servers.create') }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition focus:outline-none">
                <svg class="mr-2 h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                {{ __('Add Server') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        <!-- SSH Test Result Notification Panel -->
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

        <!-- Filter and Table Container -->
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <!-- Table Controls/Filters -->
            <div class="border-b border-slate-100 p-5 bg-slate-50/20">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">{{ __('Managed SSH Targets') }}</h3>
                        <p class="text-sm text-slate-500 mt-1">{{ __('Registered infrastructure servers available for JIT request workflows.') }}</p>
                    </div>

                    <form method="GET" action="{{ route('admin.target-servers.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <div>
                            <x-input-label for="q" :value="__('Search Keyword')" class="font-bold text-slate-700" />
                            <x-text-input id="q" name="q" type="search" class="mt-1 block w-full sm:w-80 border-slate-200 focus:border-indigo-500 focus:ring-indigo-500/20" :value="$q" placeholder="IP, Name, VMID, Username, Node..." />
                        </div>

                        <div>
                            <x-input-label for="status" :value="__('Status')" class="font-bold text-slate-700" />
                            <select id="status" name="status" class="mt-1 block w-full rounded-lg border-slate-200 bg-white text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/20 py-2 sm:w-36">
                                <option value="">{{ __('Any status') }}</option>
                                <option value="active" @selected($status === 'active')>{{ __('Active') }}</option>
                                <option value="inactive" @selected($status === 'inactive')>{{ __('Inactive') }}</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition focus:outline-none">
                                {{ __('Search') }}
                            </button>

                            @if ($q !== '' || filled($status))
                                <a href="{{ route('admin.target-servers.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition">
                                    {{ __('Reset') }}
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table of servers -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Name') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Host Connection') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Auth Type') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Credentials status') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Status') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Health') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('JIT Readiness') }}</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($targetServers as $targetServer)
                            <tr class="hover:bg-slate-50/40 transition">
                                <td class="px-6 py-4 text-sm font-bold text-slate-900">{{ $targetServer->name }}</td>
                                <td class="px-6 py-4 font-mono text-sm text-slate-600">{{ $targetServer->host }}:{{ $targetServer->port }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <x-badge :status="str_replace('_', ' ', $targetServer->auth_type)" />
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex flex-wrap gap-1.5">
                                        <span class="inline-flex items-center rounded-full bg-slate-50 px-2 py-0.5 text-xs font-semibold text-slate-600 border border-slate-200">
                                            {{ __('Pass') }}: <span class="ml-1 {{ $targetServer->hasPassword() ? 'text-emerald-700' : 'text-slate-400' }}">{{ $targetServer->hasPassword() ? __('OK') : __('No') }}</span>
                                        </span>
                                        <span class="inline-flex items-center rounded-full bg-slate-50 px-2 py-0.5 text-xs font-semibold text-slate-600 border border-slate-200">
                                            {{ __('Key') }}: <span class="ml-1 {{ $targetServer->hasPrivateKey() ? 'text-emerald-700' : 'text-slate-400' }}">{{ $targetServer->hasPrivateKey() ? __('OK') : __('No') }}</span>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <x-badge :status="$targetServer->is_active ? 'active' : 'inactive'" />
                                </td>
                                <!-- Health column -->
                                <td class="px-6 py-4 text-sm">
                                    @if ($targetServer->last_health_status)
                                        <div class="space-y-1">
                                            <x-badge :status="$targetServer->healthStatusBadgeVariant()" class="capitalize">
                                                {{ $targetServer->healthStatusLabel() }}
                                            </x-badge>
                                            <p class="text-xs text-slate-400 font-mono leading-tight">
                                                {{ $targetServer->last_health_checked_at?->timezone('Asia/Jakarta')->format('m-d H:i') }}
                                                @if ($targetServer->last_health_latency_ms !== null)
                                                    &middot; {{ $targetServer->last_health_latency_ms }}ms
                                                @endif
                                            </p>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">{{ __('Not checked') }}</span>
                                    @endif
                                </td>
                                <!-- JIT Readiness column -->
                                <td class="px-6 py-4 text-sm">
                                    @if ($targetServer->last_jit_readiness_status)
                                        <div class="space-y-1">
                                            <x-badge :status="$targetServer->jitReadinessBadgeVariant()" class="capitalize">
                                                {{ $targetServer->jitReadinessStatusLabel() }}
                                            </x-badge>
                                            <p class="text-xs text-slate-400 font-mono leading-tight">
                                                {{ $targetServer->last_jit_readiness_checked_at?->timezone('Asia/Jakarta')->format('m-d H:i') }}
                                            </p>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">{{ __('Not checked') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <div class="flex justify-end items-center gap-3">
                                        <a href="{{ route('admin.target-servers.edit', $targetServer) }}" class="font-bold text-indigo-600 hover:text-indigo-900 transition">
                                            {{ __('Edit') }}
                                        </a>

                                        <form method="POST" action="{{ route('admin.target-servers.test-connection', $targetServer) }}">
                                            @csrf
                                            <button type="submit" class="font-bold text-slate-600 hover:text-slate-900 transition">
                                                {{ __('Test Connect') }}
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.target-servers.health-check', $targetServer) }}">
                                            @csrf
                                            <button type="submit" class="font-bold text-teal-600 hover:text-teal-900 transition" title="{{ __('Run health check: TCP + SSH auth') }}">
                                                {{ __('Health Check') }}
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.target-servers.jit-readiness-check', $targetServer) }}">
                                            @csrf
                                            <button type="submit" class="font-bold text-purple-600 hover:text-purple-900 transition" title="{{ __('Verify sudo NOPASSWD for JIT credential provisioning') }}">
                                                {{ __('JIT Readiness') }}
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.target-servers.destroy', $targetServer) }}" onsubmit="return confirm('{{ __('Delete this target server permanently?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="font-bold text-rose-600 hover:text-rose-900 transition">
                                                {{ __('Delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-sm text-slate-400">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 3h13.5m-13.5-6h13.5m-13.5-3h13.5m-13.5-3h13.5" />
                                        </svg>
                                        @if ($q !== '' || filled($status))
                                            <span class="font-semibold text-slate-900">{{ __('No target servers match this search.') }}</span>
                                            <span>{{ __('Try clearing search filters.') }}</span>
                                        @else
                                            <span class="font-semibold text-slate-900">{{ __('No target servers yet') }}</span>
                                            <span>{{ __('Create one manually or import virtual machines via Proxmox node integration.') }}</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($targetServers->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $targetServers->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>


