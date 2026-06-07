<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('SSH Command') }}
            </h2>

            <a href="{{ route('sessions.show', $jitSession) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                {{ __('Back to Session') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <dl class="grid gap-6 sm:grid-cols-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Session') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">#{{ $jitSession->id }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Target Server') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $jitSession->targetServer->name }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Host') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $jitSession->targetServer->host }}:{{ $jitSession->targetServer->port }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Status') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($jitSession->effectiveStatus()) }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Usable') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $jitSession->isUsable() && $jitSession->targetServer->is_active ? __('Yes') : __('No') }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Expires At') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $jitSession->expires_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</dd>
                    </div>
                </dl>
            </div>

            @if (session('command_result'))
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('Command Result') }}</h3>
                    <p class="mt-2 text-sm {{ session('command_result.status') === 'success' ? 'text-green-700' : 'text-red-700' }}">
                        {{ session('command_result.message') }}
                    </p>

                    @if (! is_null(session('command_result.exit_code')))
                        <p class="mt-2 text-xs text-gray-500">{{ __('Exit code') }}: {{ session('command_result.exit_code') }}</p>
                    @endif

                    <pre class="mt-4 overflow-x-auto rounded bg-gray-900 p-4 text-sm text-gray-100">{{ filled(session('command_result.output')) ? session('command_result.output') : __('(no output)') }}</pre>
                </div>
            @endif

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900">{{ __('Run Command') }}</h3>

                @if ($jitSession->isUsable() && $jitSession->targetServer->is_active)
                    <form method="POST" action="{{ route('sessions.commands.store', $jitSession) }}" class="mt-6 space-y-4">
                        @csrf

                        <div>
                            <x-input-label for="command" :value="__('Command')" />
                            <textarea id="command" name="command" rows="4" class="mt-1 block w-full rounded-md border-gray-300 font-mono text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('command') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('command')" />
                        </div>

                        <div class="flex flex-wrap gap-2">
                            @foreach (['whoami', 'pwd', 'hostname', 'uptime', 'ls'] as $quickCommand)
                                <button type="button" class="rounded border border-gray-300 px-3 py-1 text-sm text-gray-700 hover:bg-gray-50" onclick="document.getElementById('command').value = @js($quickCommand);">
                                    {{ $quickCommand }}
                                </button>
                            @endforeach
                        </div>

                        <x-primary-button>
                            {{ __('Execute') }}
                        </x-primary-button>
                    </form>
                @else
                    <p class="mt-2 text-sm text-gray-600">{{ __('This session is not currently usable for command execution.') }}</p>
                @endif
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Latest Command Logs') }}</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Time') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Command') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Output') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($commandLogs as $commandLog)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                        {{ $commandLog->executed_at?->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="max-w-xs px-6 py-4 font-mono text-sm text-gray-900">
                                        {{ $commandLog->command }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                        {{ ucfirst($commandLog->status) }}
                                    </td>
                                    <td class="max-w-md px-6 py-4 text-sm text-gray-600">
                                        <pre class="whitespace-pre-wrap break-words">{{ filled($commandLog->output_excerpt) ? $commandLog->output_excerpt : __('(no output)') }}</pre>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-sm text-gray-500">
                                        {{ __('No commands have been attempted for this session yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
