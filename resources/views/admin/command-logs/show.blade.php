<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Administration') }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                    {{ __('Command Log Details') }}
                </h2>
            </div>

            <a href="{{ route('admin.command-logs.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-900 transition">
                {{ __('Back to Command Logs') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        <!-- Details grid -->
        <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-6 sm:p-8">
            <div class="flex items-center justify-between border-b border-slate-100 pb-5 mb-5">
                <h3 class="text-base font-bold text-slate-900">{{ __('Execution Metadata') }}</h3>
                <x-badge :status="$commandLog->status" />
            </div>

            <dl class="grid gap-6 sm:grid-cols-3 text-sm">
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Executed At') }}</dt>
                    <dd class="mt-1.5 font-medium text-slate-800 font-mono">{{ $commandLog->executed_at?->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') ?? __('N/A') }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Session') }}</dt>
                    <dd class="mt-1.5 font-semibold text-slate-900 font-mono">#{{ $commandLog->jit_session_id }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Exit Code') }}</dt>
                    <dd class="mt-1.5 font-semibold {{ $commandLog->exit_code === 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                        {{ is_null($commandLog->exit_code) ? __('N/A') : $commandLog->exit_code }}
                    </dd>
                </div>

                <div class="border-t border-slate-100 pt-4 mt-2 sm:col-span-3 grid gap-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('User') }}</dt>
                        <dd class="mt-1.5 text-sm font-semibold text-slate-900">{{ $commandLog->user?->name ?? __('Unknown') }}</dd>
                        @if($commandLog->user)
                            <span class="text-xs text-slate-400">{{ $commandLog->user->email }}</span>
                        @endif
                    </div>

                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Target Server') }}</dt>
                        <dd class="mt-1.5 text-sm font-semibold text-slate-900">{{ $commandLog->targetServer?->name ?? __('Unknown') }}</dd>
                        @if($commandLog->targetServer)
                            <span class="text-xs font-mono text-slate-500">{{ $commandLog->targetServer->host }}:{{ $commandLog->targetServer->port }}</span>
                        @endif
                    </div>
                </div>
            </dl>
        </div>

        <!-- Command Executed -->
        <div class="rounded-xl overflow-hidden border border-slate-950 shadow-md bg-[#0b0f19]">
            <div class="bg-slate-900 px-4 py-2.5 border-b border-slate-950/80 flex items-center justify-between">
                <span class="text-xxs font-mono text-slate-400 select-none">{{ __('command-input') }}</span>
            </div>
            <div class="p-5 font-mono text-sm text-slate-100 bg-slate-950">
                <span class="text-slate-500 mr-2 select-none">$</span>
                <span>{{ $commandLog->command }}</span>
            </div>
        </div>

        <!-- Command Output Result -->
        <div class="rounded-xl overflow-hidden border border-slate-950 shadow-md bg-[#0b0f19]">
            <div class="bg-slate-900 px-4 py-2.5 border-b border-slate-950/80 flex items-center justify-between">
                <span class="text-xxs font-mono text-slate-400 select-none">{{ __('stdout-stderr-excerpt') }}</span>
            </div>
            <div class="p-5">
                <pre class="overflow-x-auto text-xs font-mono text-slate-200 whitespace-pre-wrap leading-relaxed">{{ filled($commandLog->output_excerpt) ? $commandLog->output_excerpt : __('(no stdout or stderr output returned)') }}</pre>
            </div>
        </div>
    </div>
</x-app-layout>
