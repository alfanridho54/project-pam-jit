<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Administration') }}</p>
            <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                {{ __('Audit Logs') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Time') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Actor') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Action') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Target Context') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Description') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('IP Address') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($auditLogs as $auditLog)
                            <tr class="hover:bg-slate-50/40 transition">
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500 font-medium font-mono">
                                    {{ $auditLog->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if ($auditLog->actor)
                                        <div class="font-semibold text-slate-900">{{ $auditLog->actor->name }}</div>
                                        <div class="text-xs text-slate-400">{{ $auditLog->actor->email }}</div>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-800 border border-slate-200">{{ __('System') }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-bold text-indigo-700">
                                    {{ $auditLog->action }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-xs font-mono text-slate-600">
                                    @if ($auditLog->target_type && $auditLog->target_id)
                                        <span class="bg-slate-100 border border-slate-200 px-2 py-0.5 rounded">{{ class_basename($auditLog->target_type) }} #{{ $auditLog->target_id }}</span>
                                    @else
                                        <span class="text-slate-400 italic">{{ __('None') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 leading-normal max-w-sm">
                                    {{ $auditLog->description }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-mono text-slate-500 font-semibold">
                                    {{ $auditLog->ip_address ?? __('N/A') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-400">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622" />
                                        </svg>
                                        <span>{{ __('No audit logs have been recorded yet.') }}</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($auditLogs->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $auditLogs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
