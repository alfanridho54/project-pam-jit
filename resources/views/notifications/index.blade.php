<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('User Workspace') }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                    {{ __('Notifications') }}
                </h2>
            </div>

            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <x-secondary-button type="submit" class="border-slate-200 hover:bg-slate-50 transition">
                        {{ __('Mark All Read') }}
                    </x-secondary-button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto">
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="divide-y divide-slate-100">
                    @forelse ($notifications as $notification)
                        <div class="p-6 transition {{ $notification->read_at ? 'bg-white' : 'bg-indigo-50/20' }} relative">
                            <!-- Left border indicator for unread -->
                            @if(is_null($notification->read_at))
                                <div class="absolute left-0 top-0 bottom-0 w-1 bg-indigo-500"></div>
                            @endif

                            <div class="flex items-start justify-between gap-6">
                                <div class="space-y-2 flex-1 min-w-0">
                                    <div class="flex items-center space-x-2">
                                        <h3 class="text-sm font-bold text-slate-900">
                                            {{ $notification->data['title'] ?? __('Notification') }}
                                        </h3>
                                        @if(is_null($notification->read_at))
                                            <span class="inline-block h-2 w-2 rounded-full bg-indigo-600 animate-pulse"></span>
                                        @endif
                                    </div>

                                    <p class="text-sm text-slate-600 leading-relaxed">
                                        {{ $notification->data['message'] ?? '' }}
                                    </p>

                                    @if (! empty($notification->data['reason']))
                                        <p class="text-sm text-slate-500 bg-slate-50 border border-slate-100 rounded-lg p-3 whitespace-pre-line leading-relaxed font-semibold">
                                            {{ $notification->data['reason'] }}
                                        </p>
                                    @endif

                                    @if (! empty($notification->data['rejection_reason']))
                                        <p class="text-sm text-red-700 bg-red-50/50 border border-red-100 rounded-lg p-3 whitespace-pre-line leading-relaxed font-semibold">
                                            <span class="font-bold text-red-800">{{ __('Rejection Reason') }}:</span> {{ $notification->data['rejection_reason'] }}
                                        </p>
                                    @endif

                                    @if (! empty($notification->data['revoke_reason']))
                                        <p class="text-sm text-rose-700 bg-rose-50/50 border border-rose-100 rounded-lg p-3 whitespace-pre-line leading-relaxed font-semibold">
                                            <span class="font-bold text-rose-800">{{ __('Revocation Reason') }}:</span> {{ $notification->data['revoke_reason'] }}
                                        </p>
                                    @endif

                                    <p class="text-xxs text-slate-400 font-semibold font-mono">
                                        {{ $notification->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}
                                    </p>
                                </div>

                                <div class="flex shrink-0 items-center gap-3">
                                    @if (! empty($notification->data['url']))
                                        <a href="{{ $notification->data['url'] }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition">
                                            {{ __('Open Link') }}
                                        </a>
                                    @endif

                                    @if (is_null($notification->read_at))
                                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-bold text-indigo-700 hover:bg-indigo-100 hover:text-indigo-800 transition">
                                                {{ __('Mark read') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center text-sm text-slate-400">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0" />
                                </svg>
                                <span>{{ __('You do not have any notifications yet.') }}</span>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if ($notifications->hasPages())
                    <div class="border-t border-slate-100 px-6 py-4">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
