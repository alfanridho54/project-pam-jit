<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('JIT Session') }}
            </h2>

            <a href="{{ route('sessions.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                {{ __('Back to My Sessions') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if ($jitSession->isUsable() && $jitSession->targetServer->is_active)
                <div class="mb-6 flex justify-end">
                    <a href="{{ route('sessions.commands.index', $jitSession) }}" class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        {{ __('Open SSH Command') }}
                    </a>
                </div>
            @endif

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <dl class="grid gap-6 sm:grid-cols-2">
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
                        <dd class="mt-1 text-sm text-gray-900">{{ $jitSession->isUsable() ? __('Yes') : __('No') }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Started At') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $jitSession->started_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Expires At') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $jitSession->expires_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</dd>
                    </div>

                    @if ($jitSession->ended_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Ended At') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $jitSession->ended_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</dd>
                        </div>
                    @endif

                    @if ($jitSession->revoked_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Revoked By') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $jitSession->revokedBy?->name ?? __('Unknown') }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Revoked At') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $jitSession->revoked_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</dd>
                        </div>

                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">{{ __('Revoke Reason') }}</dt>
                            <dd class="mt-1 whitespace-pre-line text-sm text-gray-900">{{ $jitSession->revoke_reason }}</dd>
                        </div>
                    @endif

                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Access Request Reason') }}</dt>
                        <dd class="mt-1 whitespace-pre-line text-sm text-gray-900">{{ $jitSession->accessRequest->reason }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
