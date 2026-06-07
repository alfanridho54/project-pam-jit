<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ([
                    __('Pending Requests') => $summary['pending_requests'],
                    __('Active Sessions') => $summary['active_sessions'],
                    __('Expired Sessions') => $summary['expired_sessions'],
                    __('Commands Today') => $summary['command_logs_today'],
                    __('Unread Notifications') => $summary['unread_notifications'],
                ] as $label => $value)
                    <div class="bg-white p-5 shadow-sm sm:rounded-lg">
                        <div class="text-sm font-medium text-gray-500">{{ $label }}</div>
                        <div class="mt-2 text-3xl font-semibold text-gray-900">{{ $value }}</div>
                    </div>
                @endforeach
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('requests.create') }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">{{ __('Create Access Request') }}</a>
                <a href="{{ route('requests.index') }}" class="inline-flex items-center rounded-md bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50">{{ __('My Requests') }}</a>
                <a href="{{ route('sessions.index') }}" class="inline-flex items-center rounded-md bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50">{{ __('My Sessions') }}</a>
                <a href="{{ route('notifications.index') }}" class="inline-flex items-center rounded-md bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50">{{ __('Notifications') }}</a>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('My Latest Access Requests') }}</h3>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($latestAccessRequests as $accessRequest)
                                    <tr>
                                        <td class="py-3 pe-4 text-sm font-medium text-gray-900">{{ $accessRequest->targetServer->name }}</td>
                                        <td class="py-3 pe-4 text-sm text-gray-600">{{ ucfirst($accessRequest->effectiveStatus()) }}</td>
                                        <td class="py-3 text-right text-sm">
                                            <a href="{{ route('requests.show', $accessRequest) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('View') }}</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td class="py-6 text-sm text-gray-500">{{ __('No access requests yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('My Active JIT Sessions') }}</h3>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($activeJitSessions as $jitSession)
                                    <tr>
                                        <td class="py-3 pe-4 text-sm font-medium text-gray-900">{{ $jitSession->targetServer->name }}</td>
                                        <td class="py-3 pe-4 text-sm text-gray-600">{{ $jitSession->expires_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</td>
                                        <td class="py-3 text-right text-sm">
                                            <a href="{{ route('sessions.show', $jitSession) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('View') }}</a>
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
                    <h3 class="text-lg font-medium text-gray-900">{{ __('My Latest Command Logs') }}</h3>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($latestCommandLogs as $commandLog)
                                    <tr>
                                        <td class="max-w-xs py-3 pe-4 font-mono text-sm text-gray-900">{{ $commandLog->command }}</td>
                                        <td class="py-3 pe-4 text-sm text-gray-600">{{ ucfirst($commandLog->status) }}</td>
                                        <td class="py-3 text-sm text-gray-600">{{ $commandLog->executed_at?->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr><td class="py-6 text-sm text-gray-500">{{ __('No command logs yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('My Latest Notifications') }}</h3>
                    <div class="mt-4 divide-y divide-gray-200">
                        @forelse ($latestNotifications as $notification)
                            <div class="py-3">
                                <div class="text-sm font-medium text-gray-900">{{ $notification->data['title'] ?? __('Notification') }}</div>
                                <div class="mt-1 text-sm text-gray-600">{{ $notification->data['message'] ?? '' }}</div>
                                <div class="mt-1 text-xs text-gray-500">{{ $notification->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</div>
                            </div>
                        @empty
                            <div class="py-6 text-sm text-gray-500">{{ __('No notifications yet.') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
