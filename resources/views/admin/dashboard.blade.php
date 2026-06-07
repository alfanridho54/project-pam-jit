<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-gray-500">{{ __('Operations overview') }}</p>
            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900">
                {{ __('Admin Dashboard') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto space-y-8 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    __('Total Target Servers') => $summary['total_target_servers'],
                    __('Active Target Servers') => $summary['active_target_servers'],
                    __('Pending Requests') => $summary['pending_access_requests'],
                    __('Active JIT Sessions') => $summary['active_jit_sessions'],
                    __('Expired Today') => $summary['expired_sessions_today'],
                    __('Revoked Today') => $summary['revoked_sessions_today'],
                    __('Commands Today') => $summary['command_logs_today'],
                    __('Blocked Today') => $summary['blocked_command_logs_today'],
                ] as $label => $value)
                    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="text-sm font-medium text-gray-500">{{ $label }}</div>
                        <div class="mt-3 text-4xl font-semibold tracking-tight text-gray-950">{{ number_format($value) }}</div>
                    </div>
                @endforeach
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('admin.target-servers.index') }}" class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">{{ __('Manage Target Servers') }}</a>
                    <a href="{{ route('admin.access-requests.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">{{ __('View Access Requests') }}</a>
                    <a href="{{ route('admin.sessions.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">{{ __('View JIT Sessions') }}</a>
                    <a href="{{ route('admin.audit-logs.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">{{ __('View Audit Logs') }}</a>
                    <a href="{{ route('admin.command-logs.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">{{ __('View Command Logs') }}</a>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-2">
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-5 py-4">
                        <h3 class="text-base font-semibold text-gray-900">{{ __('Latest Pending Access Requests') }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('User') }}</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Target') }}</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($latestPendingAccessRequests as $accessRequest)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ $accessRequest->user->name }}</td>
                                        <td class="px-5 py-3 text-sm text-gray-600">{{ $accessRequest->targetServer->name }}</td>
                                        <td class="px-5 py-3 text-right text-sm">
                                            <a href="{{ route('admin.access-requests.show', $accessRequest) }}" class="font-medium text-indigo-600 hover:text-indigo-900">{{ __('Review') }}</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-5 py-8 text-center text-sm text-gray-500">{{ __('No pending requests.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-5 py-4">
                        <h3 class="text-base font-semibold text-gray-900">{{ __('Latest Active JIT Sessions') }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('User') }}</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Target') }}</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Expires') }}</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($latestActiveJitSessions as $jitSession)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ $jitSession->user->name }}</td>
                                        <td class="px-5 py-3 text-sm text-gray-600">{{ $jitSession->targetServer->name }}</td>
                                        <td class="px-5 py-3 text-sm text-gray-600">{{ $jitSession->expires_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</td>
                                        <td class="px-5 py-3 text-right text-sm">
                                            <a href="{{ route('admin.sessions.show', $jitSession) }}" class="font-medium text-indigo-600 hover:text-indigo-900">{{ __('View') }}</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-5 py-8 text-center text-sm text-gray-500">{{ __('No active sessions.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-5 py-4">
                        <h3 class="text-base font-semibold text-gray-900">{{ __('Latest Command Logs') }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('User') }}</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Command') }}</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Status') }}</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($latestCommandLogs as $commandLog)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ $commandLog->user?->name ?? __('Unknown') }}</td>
                                        <td class="max-w-xs truncate px-5 py-3 font-mono text-sm text-gray-600">{{ $commandLog->command }}</td>
                                        <td class="px-5 py-3 text-sm text-gray-600">{{ ucfirst($commandLog->status) }}</td>
                                        <td class="px-5 py-3 text-right text-sm">
                                            <a href="{{ route('admin.command-logs.show', $commandLog) }}" class="font-medium text-indigo-600 hover:text-indigo-900">{{ __('View') }}</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-5 py-8 text-center text-sm text-gray-500">{{ __('No command logs yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-5 py-4">
                        <h3 class="text-base font-semibold text-gray-900">{{ __('Latest Audit Logs') }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Actor') }}</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Action') }}</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Time') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($latestAuditLogs as $auditLog)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ $auditLog->actor?->name ?? __('System') }}</td>
                                        <td class="px-5 py-3 text-sm text-gray-600">{{ $auditLog->action }}</td>
                                        <td class="px-5 py-3 text-sm text-gray-600">{{ $auditLog->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-5 py-8 text-center text-sm text-gray-500">{{ __('No audit logs yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
