<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-gray-500">{{ __('User workspace') }}</p>
            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900">
                {{ __('Dashboard') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto space-y-8 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ([
                    __('Pending Requests') => $summary['pending_requests'],
                    __('Active Sessions') => $summary['active_sessions'],
                    __('Expired Sessions') => $summary['expired_sessions'],
                    __('Commands Today') => $summary['command_logs_today'],
                    __('Unread Notifications') => $summary['unread_notifications'],
                ] as $label => $value)
                    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="text-sm font-medium text-gray-500">{{ $label }}</div>
                        <div class="mt-3 text-4xl font-semibold tracking-tight text-gray-950">{{ number_format($value) }}</div>
                    </div>
                @endforeach
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('requests.create') }}" class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">{{ __('Create Access Request') }}</a>
                    <a href="{{ route('requests.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">{{ __('My Requests') }}</a>
                    <a href="{{ route('sessions.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">{{ __('My Sessions') }}</a>
                    <a href="{{ route('notifications.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">{{ __('Notifications') }}</a>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-2">
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-5 py-4">
                        <h3 class="text-base font-semibold text-gray-900">{{ __('My Latest Access Requests') }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Target') }}</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Status') }}</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($latestAccessRequests as $accessRequest)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ $accessRequest->targetServer->name }}</td>
                                        <td class="px-5 py-3 text-sm text-gray-600">{{ ucfirst($accessRequest->effectiveStatus()) }}</td>
                                        <td class="px-5 py-3 text-right text-sm">
                                            <a href="{{ route('requests.show', $accessRequest) }}" class="font-medium text-indigo-600 hover:text-indigo-900">{{ __('View') }}</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-5 py-8 text-center text-sm text-gray-500">{{ __('No access requests yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-5 py-4">
                        <h3 class="text-base font-semibold text-gray-900">{{ __('My Active JIT Sessions') }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Target') }}</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Expires') }}</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($activeJitSessions as $jitSession)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ $jitSession->targetServer->name }}</td>
                                        <td class="px-5 py-3 text-sm text-gray-600">{{ $jitSession->expires_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</td>
                                        <td class="px-5 py-3 text-right text-sm">
                                            <a href="{{ route('sessions.show', $jitSession) }}" class="font-medium text-indigo-600 hover:text-indigo-900">{{ __('View') }}</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-5 py-8 text-center text-sm text-gray-500">{{ __('No active sessions.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-5 py-4">
                        <h3 class="text-base font-semibold text-gray-900">{{ __('My Latest Command Logs') }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Command') }}</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Status') }}</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Time') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($latestCommandLogs as $commandLog)
                                    <tr class="hover:bg-gray-50">
                                        <td class="max-w-xs truncate px-5 py-3 font-mono text-sm text-gray-900">{{ $commandLog->command }}</td>
                                        <td class="px-5 py-3 text-sm text-gray-600">{{ ucfirst($commandLog->status) }}</td>
                                        <td class="px-5 py-3 text-sm text-gray-600">{{ $commandLog->executed_at?->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-5 py-8 text-center text-sm text-gray-500">{{ __('No command logs yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-5 py-4">
                        <h3 class="text-base font-semibold text-gray-900">{{ __('My Latest Notifications') }}</h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse ($latestNotifications as $notification)
                            <div class="px-5 py-4 hover:bg-gray-50">
                                <div class="text-sm font-semibold text-gray-900">{{ $notification->data['title'] ?? __('Notification') }}</div>
                                <div class="mt-1 text-sm text-gray-600">{{ $notification->data['message'] ?? '' }}</div>
                                <div class="mt-2 text-xs text-gray-500">{{ $notification->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</div>
                            </div>
                        @empty
                            <div class="px-5 py-8 text-center text-sm text-gray-500">{{ __('No notifications yet.') }}</div>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
