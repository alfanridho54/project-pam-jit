<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Administration') }}</p>
            <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                {{ __('Command Logs') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Execution Time') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('User') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Target Server') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Session') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Command') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Status') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Output Excerpt') }}</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($commandLogs as $commandLog)
                            <tr class="hover:bg-slate-50/40 transition">
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500 font-medium font-mono">
                                    {{ $commandLog->executed_at?->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if ($commandLog->user)
                                        <div class="font-semibold text-slate-900">{{ $commandLog->user->name }}</div>
                                        <div class="text-xs text-slate-400">{{ $commandLog->user->email }}</div>
                                    @else
                                        <span class="text-slate-400 italic">{{ __('Unknown') }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-slate-900">
                                    {{ $commandLog->targetServer?->name ?? __('Unknown') }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600 font-semibold font-mono">
                                    #{{ $commandLog->jit_session_id }}
                                </td>
                                <td class="px-6 py-4 max-w-xs truncate font-mono text-xs text-slate-900 bg-slate-50/20 font-semibold">
                                    {{ $commandLog->command }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <x-badge :status="$commandLog->status" />
                                </td>
                                <td class="px-6 py-4 text-xs font-mono text-slate-600 max-w-xs">
                                    @if(filled($commandLog->output_excerpt))
                                        <pre class="bg-slate-50 border border-slate-200 rounded p-2 overflow-x-auto truncate max-h-16">{{ $commandLog->output_excerpt }}</pre>
                                    @else
                                        <span class="text-slate-400 italic">{{ __('(no output)') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <a href="{{ route('admin.command-logs.show', $commandLog) }}" class="font-bold text-indigo-600 hover:text-indigo-900 transition">
                                        {{ __('View') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-sm text-slate-400">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" />
                                        </svg>
                                        <span>{{ __('No command logs have been recorded yet.') }}</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($commandLogs->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $commandLogs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
