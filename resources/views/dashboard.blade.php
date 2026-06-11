<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('User Workspace') }}</p>
            <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                {{ __('Dashboard') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6 space-y-8">
        <!-- Summary Stats Section -->
        <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-5">
            <!-- Metric Card 1: Pending Requests -->
            <div class="pam-card rounded-xl border border-slate-200 bg-white p-6 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Pending Requests') }}</span>
                    <h3 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($summary['pending_requests']) }}</h3>
                </div>
                <div class="rounded-lg bg-amber-50 p-3 text-amber-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>

            <!-- Metric Card 2: Active Sessions -->
            <div class="pam-card rounded-xl border border-slate-200 bg-white p-6 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Active Sessions') }}</span>
                    <h3 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($summary['active_sessions']) }}</h3>
                </div>
                <div class="rounded-lg bg-emerald-50 p-3 text-emerald-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                    </svg>
                </div>
            </div>

            <!-- Metric Card 3: Expired Sessions -->
            <div class="pam-card rounded-xl border border-slate-200 bg-white p-6 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Expired Sessions') }}</span>
                    <h3 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($summary['expired_sessions']) }}</h3>
                </div>
                <div class="rounded-lg bg-slate-100 p-3 text-slate-500">
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
                </div>
                <div class="rounded-lg bg-indigo-50 p-3 text-indigo-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" />
                    </svg>
                </div>
            </div>

            <!-- Metric Card 5: Unread Notifications -->
            <div class="pam-card rounded-xl border border-slate-200 bg-white p-6 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Unread Alerts') }}</span>
                    <h3 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($summary['unread_notifications']) }}</h3>
                </div>
                <div class="rounded-lg {{ $summary['unread_notifications'] > 0 ? 'bg-red-50 text-red-500' : 'bg-slate-55 bg-slate-50 text-slate-400' }}">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0M3.124 7.5A8.969 8.969 0 0 1 5.292 3m13.416 0a8.969 8.969 0 0 1 2.168 4.5" />
                    </svg>
                </div>
            </div>
        </section>

        <!-- Quick Access Section -->
        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-900">{{ __('Quick Actions') }}</h3>
                <p class="text-sm text-slate-500">{{ __('Initiate a JIT request or manage your current active access tokens.') }}</p>
            </div>
            <div class="flex flex-wrap gap-3 shrink-0">
                <a href="{{ route('requests.create') }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition focus:outline-none">
                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    {{ __('Create Access Request') }}
                </a>
                <a href="{{ route('requests.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition">
                    {{ __('My Requests') }}
                </a>
                <a href="{{ route('sessions.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition">
                    {{ __('My Sessions') }}
                </a>
            </div>
        </section>

        <!-- Dynamic Grid Content -->
        <section class="grid gap-8 xl:grid-cols-2">
            <!-- Card 1: Latest Requests -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm flex flex-col">
                <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900">{{ __('My Latest Access Requests') }}</h3>
                    <a href="{{ route('requests.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">{{ __('View All') }}</a>
                </div>
                <div class="overflow-x-auto flex-1">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Target Server') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($latestAccessRequests as $accessRequest)
                                <tr class="hover:bg-slate-50/40 transition">
                                    <td class="px-6 py-4 text-sm font-semibold text-slate-900">
                                        {{ $accessRequest->targetServer->name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <x-badge :status="$accessRequest->effectiveStatus()" />
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <a href="{{ route('requests.show', $accessRequest) }}" class="font-semibold text-indigo-600 hover:text-indigo-900 transition">{{ __('View') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center text-sm text-slate-400">
                                        <div class="flex flex-col items-center justify-center space-y-2">
                                            <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                            </svg>
                                            <span>{{ __('No access requests yet.') }}</span>
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
                    <h3 class="text-base font-bold text-slate-900">{{ __('My Active JIT Sessions') }}</h3>
                    <a href="{{ route('sessions.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">{{ __('View All') }}</a>
                </div>
                <div class="overflow-x-auto flex-1">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Target Server') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Expires') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($activeJitSessions as $jitSession)
                                <tr class="hover:bg-slate-50/40 transition">
                                    <td class="px-6 py-4 text-sm font-semibold text-slate-900">
                                        {{ $jitSession->targetServer->name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        <div class="flex items-center space-x-1.5 font-medium">
                                            <span class="inline-block h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                            <span>{{ $jitSession->expires_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <a href="{{ route('sessions.show', $jitSession) }}" class="font-semibold text-indigo-600 hover:text-indigo-900 transition">{{ __('Access Console') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center text-sm text-slate-400">
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
                <div class="border-b border-slate-100 px-6 py-4">
                    <h3 class="text-base font-bold text-slate-900">{{ __('My Latest Command Logs') }}</h3>
                </div>
                <div class="overflow-x-auto flex-1">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Command') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Time') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($latestCommandLogs as $commandLog)
                                <tr class="hover:bg-slate-50/40 transition">
                                    <td class="px-6 py-4 max-w-xs truncate font-mono text-xs text-slate-700 bg-slate-50/20">
                                        {{ $commandLog->command }}
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <x-badge :status="$commandLog->status" />
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-500 font-medium">
                                        {{ $commandLog->executed_at?->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center text-sm text-slate-400">
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

            <!-- Card 4: Latest Notifications -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm flex flex-col">
                <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900">{{ __('My Latest Notifications') }}</h3>
                    <a href="{{ route('notifications.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">{{ __('View All') }}</a>
                </div>
                <div class="divide-y divide-slate-100 flex-1">
                    @forelse ($latestNotifications as $notification)
                        <div class="px-6 py-4 hover:bg-slate-50/40 transition">
                            <div class="flex items-start justify-between">
                                <h4 class="text-sm font-semibold text-slate-900">{{ $notification->data['title'] ?? __('Notification') }}</h4>
                                <span class="text-xxs font-medium text-slate-400">{{ $notification->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</span>
                            </div>
                            <p class="mt-1 text-sm text-slate-600 leading-relaxed">{{ $notification->data['message'] ?? '' }}</p>
                        </div>
                    @empty
                        <div class="px-6 py-12 text-center text-sm text-slate-400">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                                </svg>
                                <span>{{ __('No notifications yet.') }}</span>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
