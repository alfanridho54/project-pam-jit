<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Notifications') }}
            </h2>

            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf

                    <x-secondary-button>
                        {{ __('Mark All Read') }}
                    </x-secondary-button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="divide-y divide-gray-200">
                    @forelse ($notifications as $notification)
                        <div class="p-6 {{ $notification->read_at ? 'bg-white' : 'bg-indigo-50' }}">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900">
                                        {{ $notification->data['title'] ?? __('Notification') }}
                                    </h3>

                                    <p class="mt-1 text-sm text-gray-700">
                                        {{ $notification->data['message'] ?? '' }}
                                    </p>

                                    @if (! empty($notification->data['reason']))
                                        <p class="mt-3 whitespace-pre-line text-sm text-gray-600">
                                            {{ $notification->data['reason'] }}
                                        </p>
                                    @endif

                                    @if (! empty($notification->data['rejection_reason']))
                                        <p class="mt-3 whitespace-pre-line text-sm text-gray-600">
                                            {{ __('Reason') }}: {{ $notification->data['rejection_reason'] }}
                                        </p>
                                    @endif

                                    @if (! empty($notification->data['revoke_reason']))
                                        <p class="mt-3 whitespace-pre-line text-sm text-gray-600">
                                            {{ __('Reason') }}: {{ $notification->data['revoke_reason'] }}
                                        </p>
                                    @endif

                                    <p class="mt-3 text-xs text-gray-500">
                                        {{ $notification->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}
                                    </p>
                                </div>

                                <div class="flex shrink-0 items-center gap-3">
                                    @if (! empty($notification->data['url']))
                                        <a href="{{ $notification->data['url'] }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                            {{ __('Open') }}
                                        </a>
                                    @endif

                                    @if (is_null($notification->read_at))
                                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                            @csrf

                                            <button type="submit" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                                                {{ __('Mark read') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center text-sm text-gray-500">
                            {{ __('You do not have any notifications yet.') }}
                        </div>
                    @endforelse
                </div>

                @if ($notifications->hasPages())
                    <div class="border-t border-gray-200 px-6 py-4">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
