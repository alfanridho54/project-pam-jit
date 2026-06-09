<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('JIT Session') }}
            </h2>

            <a href="{{ route('admin.sessions.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                {{ __('Back to JIT Sessions') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="space-y-6">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <dl class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('User') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $jitSession->user->name }} &lt;{{ $jitSession->user->email }}&gt;</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Status') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($jitSession->effectiveStatus()) }}</dd>
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

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Temporary Credential Lifecycle') }}</h3>

                    <dl class="mt-6 grid gap-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Uses Temporary Credential') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $jitSession->uses_temporary_credential ? __('Yes') : __('No') }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Temporary Username') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $jitSession->temporary_username ?? __('None') }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Credential Status') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $jitSession->temporary_credential_status ? str_replace('_', ' ', ucfirst($jitSession->temporary_credential_status)) : __('None') }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Created At') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $jitSession->temporary_credential_created_at?->timezone('Asia/Jakarta')->format('Y-m-d H:i') ?? __('None') }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Disabled At') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $jitSession->temporary_credential_disabled_at?->timezone('Asia/Jakarta')->format('Y-m-d H:i') ?? __('None') }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Deleted At') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $jitSession->temporary_credential_deleted_at?->timezone('Asia/Jakarta')->format('Y-m-d H:i') ?? __('None') }}</dd>
                        </div>

                        @if ($jitSession->temporary_credential_error)
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">{{ __('Safe Error') }}</dt>
                                <dd class="mt-1 whitespace-pre-line text-sm text-red-700">{{ $jitSession->temporary_credential_error }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                @if ($jitSession->isActive())
                    <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Revoke Session') }}</h3>

                        <form method="POST" action="{{ route('admin.sessions.revoke', $jitSession) }}" class="mt-4 space-y-4">
                            @csrf

                            <div>
                                <x-input-label for="revoke_reason" :value="__('Revoke Reason')" />
                                <textarea id="revoke_reason" name="revoke_reason" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('revoke_reason') }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('revoke_reason')" />
                            </div>

                            <x-danger-button>
                                {{ __('Revoke Session') }}
                            </x-danger-button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
