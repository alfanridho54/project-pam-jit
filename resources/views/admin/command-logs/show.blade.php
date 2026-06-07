<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Command Log') }}
            </h2>

            <a href="{{ route('admin.command-logs.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                {{ __('Back to Command Logs') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <dl class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Executed At') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $commandLog->executed_at?->timezone('Asia/Jakarta')->format('Y-m-d H:i') ?? __('N/A') }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Status') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($commandLog->status) }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('User') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $commandLog->user?->name ?? __('Unknown') }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Target Server') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $commandLog->targetServer?->name ?? __('Unknown') }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Session') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">#{{ $commandLog->jit_session_id }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Exit Code') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ is_null($commandLog->exit_code) ? __('N/A') : $commandLog->exit_code }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <h3 class="text-sm font-semibold text-gray-900">{{ __('Command') }}</h3>
                <pre class="mt-4 overflow-x-auto rounded bg-gray-900 p-4 text-sm text-gray-100">{{ $commandLog->command }}</pre>
            </div>

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <h3 class="text-sm font-semibold text-gray-900">{{ __('Output') }}</h3>
                <pre class="mt-4 overflow-x-auto whitespace-pre-wrap rounded bg-gray-900 p-4 text-sm text-gray-100">{{ filled($commandLog->output_excerpt) ? $commandLog->output_excerpt : __('(no output)') }}</pre>
            </div>
        </div>
    </div>
</x-app-layout>
