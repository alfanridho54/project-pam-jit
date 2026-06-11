<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('My Access') }}</p>
            <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                {{ __('My Sessions') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Target Server') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Started') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Expires') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Status') }}</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($jitSessions as $jitSession)
                            <tr class="hover:bg-slate-50/40 transition">
                                <td class="px-6 py-4 text-sm font-semibold text-slate-900">
                                    {{ $jitSession->targetServer->name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500 font-medium font-mono">
                                    {{ $jitSession->started_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500 font-medium font-mono">
                                    {{ $jitSession->expires_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <x-badge :status="$jitSession->effectiveStatus()" />
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <a href="{{ route('sessions.show', $jitSession) }}" class="font-semibold text-indigo-600 hover:text-indigo-900 transition">
                                        {{ __('Access Console') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-400">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                        </svg>
                                        <span>{{ __('You do not have any JIT sessions yet.') }}</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($jitSessions->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $jitSessions->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
