<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Operations overview') }}</p>
            <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                {{ __('Admin Dashboard') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6 space-y-8">
        <!-- Summary Stats Section -->
        <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Metric Card 1: Total Target Servers -->
            <div class="pam-card rounded-xl border border-slate-200 bg-white p-6 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Total Target Servers') }}</span>
                    <h3 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($summary['total_target_servers']) }}</h3>
                    <p class="text-xs text-slate-400 mt-1"><span class="text-emerald-600 font-semibold">{{ $summary['active_target_servers'] }}</span> {{ __('active servers') }}</p>
                </div>
                <div class="rounded-lg bg-indigo-50 p-3 text-indigo-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 3h13.5m-13.5-6h13.5m-13.5-3h13.5m-13.5-3h13.5" />
                    </svg>
                </div>
            </div>

            <!-- Metric Card 2: Pending Requests -->
            <div class="pam-card rounded-xl border border-slate-200 bg-white p-6 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Pending Requests') }}</span>
                    <h3 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($summary['pending_access_requests']) }}</h3>
                    <p class="text-xs text-slate-400 mt-1">{{ __('Awaiting approval review') }}</p>
                </div>
                <div class="rounded-lg {{ $summary['pending_access_requests'] > 0 ? 'bg-amber-50 text-amber-600 animate-pulse' : 'bg-slate-50 text-slate-400' }}">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>

            <!-- Metric Card 3: Active Sessions -->
            <div class="pam-card rounded-xl border border-slate-200 bg-white p-6 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Active JIT Sessions') }}</span>
                    <h3 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($summary['active_jit_sessions']) }}</h3>
                    <p class="text-xs text-slate-400 mt-1"><span class="text-rose-600 font-semibold">{{ $summary['revoked_sessions_today'] }}</span> {{ __('revoked today') }}</p>
                </div>
                <div class="rounded-lg bg-emerald-50 p-3 text-emerald-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                </div>
            </div>

            <!-- Metric Card 4: Commands Today -->
            <div class="pam-card rounded-xl border border-slate-200 bg-white p-6 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Commands Today') }}</span>
                    <h3 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($summary['command_logs_today']) }}</h3>
                    <p class="text-xs text-slate-400 mt-1"><span class="text-rose-600 font-semibold">{{ $summary['blocked_command_logs_today'] }}</span> {{ __('blocked by policy') }}</p>
                </div>
                <div class="rounded-lg bg-slate-100 p-3 text-slate-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" />
                    </svg>
                </div>
            </div>
        </section>

        <!-- Quick Access Section -->
        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-900">{{ __('Administrative Utilities') }}</h3>
                <p class="text-sm text-slate-500">{{ __('Provision target nodes, view audit telemetry, check command security logs.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2.5 shrink-0">
                <a href="{{ route('admin.target-servers.index') }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition focus:outline-none">
                    {{ __('Manage Target Servers') }}
                </a>
                <a href="{{ route('admin.access-requests.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition">
                    {{ __('View Access Requests') }}
                </a>
                <a href="{{ route('admin.sessions.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition">
                    {{ __('View JIT Sessions') }}
                </a>
                <a href="{{ route('admin.audit-logs.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition">
                    {{ __('View Audit Logs') }}
                </a>
                <a href="{{ route('admin.command-logs.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition">
                    {{ __('View Command Logs') }}
                </a>
            </div>
        </section>

        <!-- Dynamic Grid Content -->
        <section class="grid gap-8 xl:grid-cols-2">
            <!-- Card 1: Pending Access Requests -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm flex flex-col">
                <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900">{{ __('Latest Pending Access Requests') }}</h3>
                    <a href="{{ route('admin.access-requests.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">{{ __('View All') }}</a>
                </div>
                <div class="overflow-x-auto flex-1">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('User') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Target') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($latestPendingAccessRequests as $accessRequest)
                                <tr class="hover:bg-slate-50/40 transition">
                                    <td class="px-6 py-4 text-sm font-semibold text-slate-900">
                                        {{ $accessRequest->user->name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600 font-medium">
                                        {{ $accessRequest->targetServer->name }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <a href="{{ route('admin.access-requests.show', $accessRequest) }}" class="font-bold text-indigo-600 hover:text-indigo-900 transition">{{ __('Review') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center text-sm text-slate-400">
                                        <div class="flex flex-col items-center justify-center space-y-2">
                                            <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-.621-.504-1.125-1.125-1.125H9.75M3 5.25h18M3 12h18M3 18.75h18" />
                                            </svg>
                                            <span>{{ __('No pending requests.') }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Card 2: Active JIT Sessions -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm flex flex-col">
                <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900">{{ __('Latest Active JIT Sessions') }}</h3>
                    <a href="{{ route('admin.sessions.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">{{ __('View All') }}</a>
                </div>
                <div class="overflow-x-auto flex-1">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('User') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Target') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Expires') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($latestActiveJitSessions as $jitSession)
                                <tr class="hover:bg-slate-50/40 transition">
                                    <td class="px-6 py-4 text-sm font-semibold text-slate-900">
                                        {{ $jitSession->user->name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600 font-medium">
                                        {{ $jitSession->targetServer->name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        <div class="flex items-center space-x-1.5 font-medium">
                                            <span class="inline-block h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                            <span>{{ $jitSession->expires_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <a href="{{ route('admin.sessions.show', $jitSession) }}" class="font-bold text-indigo-600 hover:text-indigo-900 transition">{{ __('View') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-sm text-slate-400">
                                        <div class="flex flex-col items-center justify-center space-y-2">
                                            <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                            </svg>
                                            <span>{{ __('No active sessions.') }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Card 3: Latest Command Logs -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm flex flex-col">
                <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900">{{ __('Latest Command Logs') }}</h3>
                    <a href="{{ route('admin.command-logs.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">{{ __('View All') }}</a>
                </div>
                <div class="overflow-x-auto flex-1">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('User') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Command') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($latestCommandLogs as $commandLog)
                                <tr class="hover:bg-slate-50/40 transition">
                                    <td class="px-6 py-4 text-sm font-semibold text-slate-900">
                                        {{ $commandLog->user?->name ?? __('Unknown') }}
                                    </td>
                                    <td class="px-6 py-4 max-w-xs truncate font-mono text-xs text-slate-700 bg-slate-50/20">
                                        {{ $commandLog->command }}
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <x-badge :status="$commandLog->status" />
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <a href="{{ route('admin.command-logs.show', $commandLog) }}" class="font-bold text-indigo-600 hover:text-indigo-900 transition">{{ __('View') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-sm text-slate-400">
                                        <div class="flex flex-col items-center justify-center space-y-2">
                                            <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" />
                                            </svg>
                                            <span>{{ __('No command logs yet.') }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Card 4: Latest Audit Logs -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm flex flex-col">
                <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900">{{ __('Latest Audit Logs') }}</h3>
                    <a href="{{ route('admin.audit-logs.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">{{ __('View All') }}</a>
                </div>
                <div class="overflow-x-auto flex-1">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Actor') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Action') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Time') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($latestAuditLogs as $auditLog)
                                <tr class="hover:bg-slate-50/40 transition">
                                    <td class="px-6 py-4 text-sm font-semibold text-slate-900">
                                        {{ $auditLog->actor?->name ?? __('System') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700 leading-normal font-medium">
                                        {{ $auditLog->action }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-500 font-medium">
                                        {{ $auditLog->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center text-sm text-slate-400">
                                        <div class="flex flex-col items-center justify-center space-y-2">
                                            <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622" />
                                            </svg>
                                            <span>{{ __('No audit logs yet.') }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
