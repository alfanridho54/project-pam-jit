<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('My Access') }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                    {{ __('My Requests') }}
                </h2>
            </div>

            <a href="{{ route('requests.create') }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition focus:outline-none">
                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                {{ __('New Request') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Target Server') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Duration') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Status') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Requested At') }}</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($accessRequests as $accessRequest)
                            <tr class="hover:bg-slate-50/40 transition">
                                <td class="px-6 py-4 text-sm font-semibold text-slate-900">
                                    {{ $accessRequest->targetServer->name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 font-medium">
                                    {{ $accessRequest->formattedDuration() }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <x-badge :status="$accessRequest->effectiveStatus()" />
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500 font-medium">
                                    {{ $accessRequest->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <a href="{{ route('requests.show', $accessRequest) }}" class="font-semibold text-indigo-600 hover:text-indigo-900 transition">
                                        {{ __('View Details') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-400">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                        </svg>
                                        <span>{{ __('You have not submitted any access requests yet.') }}</span>
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
