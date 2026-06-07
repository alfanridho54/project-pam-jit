<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
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
                    <div class="bg-white p-5 shadow-sm sm:rounded-lg">
                        <div class="text-sm font-medium text-gray-500">{{ $label }}</div>
                        <div class="mt-2 text-3xl font-semibold text-gray-900">{{ $value }}</div>
                    </div>
                @endforeach
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.target-servers.index') }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">{{ __('Manage Target Servers') }}</a>
                <a href="{{ route('admin.access-requests.index') }}" class="inline-flex items-center rounded-md bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50">{{ __('View Access Requests') }}</a>
                <a href="{{ route('admin.sessions.index') }}" class="inline-flex items-center rounded-md bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50">{{ __('View JIT Sessions') }}</a>
                <a href="{{ route('admin.audit-logs.index') }}" class="inline-flex items-center rounded-md bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50">{{ __('View Audit Logs') }}</a>
                <a href="{{ route('admin.command-logs.index') }}" class="inline-flex items-center rounded-md bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50">{{ __('View Command Logs') }}</a>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Latest Pending Access Requests') }}</h3>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($latestPendingAccessRequests as $accessRequest)
                                    <tr>
                                        <td class="py-3 pe-4 text-sm font-medium text-gray-900">{{ $accessRequest->user->name }}</td>
                                        <td class="py-3 pe-4 text-sm text-gray-600">{{ $accessRequest->targetServer->name }}</td>
                                        <td class="py-3 text-right text-sm">
                                            <a href="{{ route('admin.access-requests.show', $accessRequest) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('Review') }}</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td class="py-6 text-sm text-gray-500">{{ __('No pending requests.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Latest Active JIT Sessions') }}</h3>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($latestActiveJitSessions as $jitSession)
                                    <tr>
                                        <td class="py-3 pe-4 text-sm font-medium text-gray-900">{{ $jitSession->user->name }}</td>
                                        <td class="py-3 pe-4 text-sm text-gray-600">{{ $jitSession->targetServer->name }}</td>
                                        <td class="py-3 pe-4 text-sm text-gray-600">{{ $jitSession->expires_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</td>
                                        <td class="py-3 text-right text-sm">
                                            <a href="{{ route('admin.sessions.show', $jitSession) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('View') }}</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td class="py-6 text-sm text-gray-500">{{ __('No active sessions.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Latest Command Logs') }}</h3>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($latestCommandLogs as $commandLog)
                                    <tr>
                                        <td class="py-3 pe-4 text-sm font-medium text-gray-900">{{ $commandLog->user?->name ?? __('Unknown') }}</td>
                                        <td class="max-w-xs py-3 pe-4 font-mono text-sm text-gray-600">{{ $commandLog->command }}</td>
                                        <td class="py-3 pe-4 text-sm text-gray-600">{{ ucfirst($commandLog->status) }}</td>
                                        <td class="py-3 text-right text-sm">
                                            <a href="{{ route('admin.command-logs.show', $commandLog) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('View') }}</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td class="py-6 text-sm text-gray-500">{{ __('No command logs yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Latest Audit Logs') }}</h3>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($latestAuditLogs as $auditLog)
                                    <tr>
                                        <td class="py-3 pe-4 text-sm font-medium text-gray-900">{{ $auditLog->actor?->name ?? __('System') }}</td>
                                        <td class="py-3 pe-4 text-sm text-gray-600">{{ $auditLog->action }}</td>
                                        <td class="py-3 text-sm text-gray-600">{{ $auditLog->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr><td class="py-6 text-sm text-gray-500">{{ __('No audit logs yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
