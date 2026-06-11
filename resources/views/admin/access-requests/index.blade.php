<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Administration') }}</p>
            <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                {{ __('Access Requests') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Requester') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Target Server') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Duration') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Status') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Requested') }}</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($accessRequests as $accessRequest)
                            <tr class="hover:bg-slate-50/40 transition">
                                <td class="px-6 py-4 text-sm text-slate-900">
                                    <div class="font-semibold text-slate-900">{{ $accessRequest->user->name }}</div>
                                    <div class="text-xs text-slate-400">{{ $accessRequest->user->email }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-slate-900">
                                    {{ $accessRequest->targetServer->name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 font-medium">
                                    {{ $accessRequest->formattedDuration() }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <x-badge :status="$accessRequest->effectiveStatus()" />
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500 font-medium font-mono">
                                    {{ $accessRequest->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <a href="{{ route('admin.access-requests.show', $accessRequest) }}" class="font-semibold text-indigo-600 hover:text-indigo-900 transition">
                                        {{ __('Review') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-400">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-.621-.504-1.125-1.125-1.125H9.75M3 5.25h18M3 12h18M3 18.75h18" />
                                        </svg>
                                        <span>{{ __('No access requests have been submitted yet.') }}</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($accessRequests->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $accessRequests->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
