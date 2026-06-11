<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('My Access') }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                    {{ __('SSH Command Console') }}
                </h2>
            </div>

            <a href="{{ route('sessions.show', $jitSession) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-900 transition">
                {{ __('Back to Session Profile') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        <!-- Target Server quick summary card -->
        <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-5">
            <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-6 text-sm">
                <div>
                    <dt class="text-xxs font-bold uppercase tracking-wider text-slate-400">{{ __('Session') }}</dt>
                    <dd class="mt-1 font-semibold text-slate-900">#{{ $jitSession->id }}</dd>
                </div>
                <div>
                    <dt class="text-xxs font-bold uppercase tracking-wider text-slate-400">{{ __('Target Server') }}</dt>
                    <dd class="mt-1 font-semibold text-slate-900 truncate">{{ $jitSession->targetServer->name }}</dd>
                </div>
                <div>
                    <dt class="text-xxs font-bold uppercase tracking-wider text-slate-400">{{ __('Host IP/Port') }}</dt>
                    <dd class="mt-1 font-mono text-slate-600 truncate">{{ $jitSession->targetServer->host }}:{{ $jitSession->targetServer->port }}</dd>
                </div>
                <div>
                    <dt class="text-xxs font-bold uppercase tracking-wider text-slate-400">{{ __('Status') }}</dt>
                    <dd class="mt-1 font-semibold text-slate-900"><x-badge :status="$jitSession->effectiveStatus()" /></dd>
                </div>
                <div>
                    <dt class="text-xxs font-bold uppercase tracking-wider text-slate-400">{{ __('Active') }}</dt>
                    <dd class="mt-1 font-semibold {{ $jitSession->isUsable() ? 'text-emerald-600' : 'text-slate-500' }}">
                        {{ $jitSession->isUsable() && $jitSession->targetServer->is_active ? __('Yes') : __('No') }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xxs font-bold uppercase tracking-wider text-slate-400">{{ __('Expires At') }}</dt>
                    <dd class="mt-1 font-semibold text-slate-900 font-mono text-xs">{{ $jitSession->expires_at->timezone('Asia/Jakarta')->format('H:i:s') }}</dd>
                </div>
            </dl>
        </div>

        <!-- Command Result Terminal Mockup -->
        @if (session('command_result'))
            <div class="rounded-xl overflow-hidden border border-slate-950 shadow-lg bg-[#0b0f19]">
                <!-- Terminal Header Bar -->
                <div class="bg-slate-900 px-4 py-3 border-b border-slate-950/80 flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="inline-block h-3.5 w-3.5 rounded-full bg-rose-500"></span>
                        <span class="inline-block h-3.5 w-3.5 rounded-full bg-amber-500"></span>
                        <span class="inline-block h-3.5 w-3.5 rounded-full bg-emerald-500"></span>
                    </div>
                    <span class="text-xs font-mono text-slate-500 select-none">{{ __('ssh-session-stream') }}</span>
                    <div class="w-12"></div>
                </div>

                <div class="p-6 space-y-4">
                    <div class="flex items-start">
                        <span class="text-slate-500 select-none mr-2 font-mono">$</span>
                        <p class="text-sm font-semibold font-mono text-slate-200">{{ session('command_result.message') }}</p>
                    </div>

                    @if (! is_null(session('command_result.exit_code')))
                        <div class="text-xs font-semibold font-mono">
                            <span class="text-slate-500">{{ __('exit-status:') }}</span>
                            <span class="{{ session('command_result.exit_code') === 0 ? 'text-emerald-400' : 'text-rose-400' }}">{{ session('command_result.exit_code') }}</span>
                        </div>
                    @endif

                    <div class="relative bg-slate-950 rounded-lg p-4 border border-slate-800 shadow-inner">
                        <pre class="overflow-x-auto text-xs font-mono text-slate-100 whitespace-pre-wrap leading-relaxed">{{ filled(session('command_result.output')) ? session('command_result.output') : __('(no command output returned)') }}</pre>
                    </div>
                </div>
            </div>
        @endif

        <!-- Execute Terminal Form -->
        <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-6">
            <h3 class="text-lg font-bold text-slate-900">{{ __('Run Interactive Command') }}</h3>
            <p class="text-sm text-slate-500 mt-1 mb-6">{{ __('Submit remote Unix commands to run secure operations during the active access window.') }}</p>

            @if ($jitSession->isUsable() && $jitSession->targetServer->is_active)
                <form method="POST" action="{{ route('sessions.commands.store', $jitSession) }}" class="space-y-5">
                    @csrf

                    <div>
                        <x-input-label for="command" :value="__('Terminal Command Input')" class="font-bold text-slate-700" />
                        <div class="mt-1 rounded-lg border border-slate-300 focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500 bg-slate-950 overflow-hidden">
                            <textarea id="command" name="command" rows="4" class="block w-full border-0 bg-transparent py-3 px-4 font-mono text-sm text-slate-200 placeholder-slate-600 focus:ring-0" placeholder="e.g. systemctl status apache2" required>{{ old('command') }}</textarea>
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('command')" />
                    </div>

                    <!-- Shortcut commands pills -->
                    <div class="space-y-2">
                        <span class="block text-xxs font-bold uppercase tracking-wider text-slate-400">{{ __('Quick Shortcuts') }}</span>
                        <div class="flex flex-wrap gap-2">
                            @foreach (['whoami', 'pwd', 'hostname', 'uptime', 'ls -la', 'df -h', 'free -m'] as $quickCommand)
                                <button type="button" class="rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 hover:border-slate-300 px-3.5 py-1.5 text-xs font-semibold text-slate-700 font-mono transition" onclick="document.getElementById('command').value = @js($quickCommand);">
                                    {{ $quickCommand }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end">
                        <x-primary-button class="bg-indigo-600 hover:bg-indigo-500 px-5 py-2.5 text-sm">
                            <svg class="mr-2 h-4 w-4 text-indigo-200" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" />
                            </svg>
                            {{ __('Execute Command') }}
                        </x-primary-button>
                    </div>
                </form>
            @else
                <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800 shadow-sm flex items-start">
                    <svg class="h-5 w-5 text-red-500 mr-3 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <div>{{ __('This JIT session has expired, been revoked, or is currently disabled. Interactive commands are disabled.') }}</div>
                </div>
            @endif
        </div>

        <!-- History Table of command logs -->
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-5">
                <h3 class="text-base font-bold text-slate-900">{{ __('Session Command Execution Log') }}</h3>
                <p class="text-sm text-slate-500 mt-1">{{ __('Historical record of commands attempted under this session context.') }}</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Execution Time') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Command') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Status') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Output Excerpt') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($commandLogs as $commandLog)
                            <tr class="hover:bg-slate-50/40 transition">
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500 font-medium font-mono">
                                    {{ $commandLog->executed_at?->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') }}
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-900 bg-slate-50/20 font-semibold">
                                    {{ $commandLog->command }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <x-badge :status="$commandLog->status" />
                                </td>
                                <td class="px-6 py-4 text-xs font-mono text-slate-600 max-w-md">
                                    @if(filled($commandLog->output_excerpt))
                                        <pre class="bg-slate-50 border border-slate-200 rounded p-2 overflow-x-auto truncate max-h-24">{{ $commandLog->output_excerpt }}</pre>
                                    @else
                                        <span class="text-slate-400 italic">{{ __('(no output returned)') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-sm text-slate-400">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" />
                                        </svg>
                                        <span>{{ __('No commands have been attempted for this session yet.') }}</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
