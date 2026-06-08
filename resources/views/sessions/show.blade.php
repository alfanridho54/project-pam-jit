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

            @if ($jitSession->isUsable())
                <div class="mt-6 bg-white p-6 shadow-sm sm:rounded-lg">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('SFTP / WinSCP Access') }}</h3>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('Use these details with WinSCP or another SFTP client while this JIT session is active.') }}
                            </p>
                        </div>

                        <a href="{{ route('sessions.sftp-profile.download', $jitSession) }}" class="inline-flex items-center justify-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            {{ __('Download WinSCP Profile') }}
                        </a>
                    </div>

                    <dl class="mt-6 grid gap-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Protocol') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ __('SFTP') }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Target Server') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $jitSession->targetServer->name }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Host') }}</dt>
                            <dd class="mt-1 flex rounded-md shadow-sm">
                                <input id="sftp-host" type="text" readonly value="{{ $jitSession->targetServer->host }}" class="block w-full rounded-l-md border-gray-300 bg-gray-50 text-sm text-gray-900 focus:border-indigo-500 focus:ring-indigo-500">
                                <button type="button" data-copy-target="sftp-host" class="inline-flex items-center rounded-r-md border border-l-0 border-gray-300 bg-white px-3 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">
                                    {{ __('Copy') }}
                                </button>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Port') }}</dt>
                            <dd class="mt-1 flex rounded-md shadow-sm">
                                <input id="sftp-port" type="text" readonly value="{{ $jitSession->targetServer->port }}" class="block w-full rounded-l-md border-gray-300 bg-gray-50 text-sm text-gray-900 focus:border-indigo-500 focus:ring-indigo-500">
                                <button type="button" data-copy-target="sftp-port" class="inline-flex items-center rounded-r-md border border-l-0 border-gray-300 bg-white px-3 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">
                                    {{ __('Copy') }}
                                </button>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('SSH Username') }}</dt>
                            <dd class="mt-1 flex rounded-md shadow-sm">
                                <input id="sftp-username" type="text" readonly value="{{ $jitSession->targetServer->ssh_username }}" class="block w-full rounded-l-md border-gray-300 bg-gray-50 text-sm text-gray-900 focus:border-indigo-500 focus:ring-indigo-500">
                                <button type="button" data-copy-target="sftp-username" class="inline-flex items-center rounded-r-md border border-l-0 border-gray-300 bg-white px-3 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">
                                    {{ __('Copy') }}
                                </button>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Session Expires At') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $jitSession->expires_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</dd>
                        </div>
                    </dl>

                    <div class="mt-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        {{ __('Credentials are managed by PAM and are not shown or included in the downloaded profile. File transfer access is only allowed during the active JIT session. After expiry or revocation, this access should no longer be considered valid.') }}
                    </div>
                </div>

                <script>
                    document.querySelectorAll('[data-copy-target]').forEach((button) => {
                        button.addEventListener('click', async () => {
                            const input = document.getElementById(button.dataset.copyTarget);

                            if (! input) {
                                return;
                            }

                            await navigator.clipboard.writeText(input.value);
                            button.textContent = @json(__('Copied'));

                            setTimeout(() => {
                                button.textContent = @json(__('Copy'));
                            }, 1500);
                        });
                    });
                </script>
            @endif
        </div>
    </div>
</x-app-layout>
