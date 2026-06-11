<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('My Access') }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                    {{ __('JIT Session Console') }}
                </h2>
            </div>

            <div class="flex items-center space-x-3 shrink-0">
                <a href="{{ route('sessions.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition">
                    {{ __('Back to My Sessions') }}
                </a>

                @if ($jitSession->isUsable() && $jitSession->targetServer->is_active)
                    <a href="{{ route('sessions.commands.index', $jitSession) }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition focus:outline-none">
                        <svg class="mr-2 h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" />
                        </svg>
                        {{ __('Open SSH Command') }}
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-8">
        <!-- Main details grid -->
        <div class="grid gap-8 lg:grid-cols-3">
            <!-- Sidebar stats / status panel -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-6">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Session Status') }}</span>
                        <x-badge :status="$jitSession->effectiveStatus()" />
                    </div>

                    <div class="space-y-4">
                        <div>
                            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Target Server') }}</span>
                            <span class="mt-1 block text-sm font-semibold text-slate-900">{{ $jitSession->targetServer->name }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Host Connection') }}</span>
                            <span class="mt-1 block text-sm font-mono text-slate-600">{{ $jitSession->targetServer->host }}:{{ $jitSession->targetServer->port }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Usable Status') }}</span>
                            <span class="mt-1 inline-flex items-center text-sm font-semibold {{ $jitSession->isUsable() ? 'text-emerald-700' : 'text-slate-500' }}">
                                <span class="mr-1.5 h-2 w-2 rounded-full {{ $jitSession->isUsable() ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                {{ $jitSession->isUsable() ? __('Active & Open') : __('Closed / Suspended') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-6">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-4">{{ __('Timeline & Logs') }}</h3>
                    <div class="space-y-4">
                        <div>
                            <span class="block text-xs font-semibold text-slate-400">{{ __('Started At') }}</span>
                            <span class="mt-0.5 block text-sm font-medium text-slate-800 font-mono">{{ $jitSession->started_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-slate-400">{{ __('Expires At') }}</span>
                            <span class="mt-0.5 block text-sm font-medium text-slate-800 font-mono">{{ $jitSession->expires_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</span>
                        </div>
                        @if ($jitSession->ended_at)
                            <div>
                                <span class="block text-xs font-semibold text-slate-400">{{ __('Ended At') }}</span>
                                <span class="mt-0.5 block text-sm font-medium text-slate-800 font-mono">{{ $jitSession->ended_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</span>
                            </div>
                        @endif

                        @if ($jitSession->revoked_at)
                            <div class="border-t border-slate-100 pt-4 mt-4 space-y-3">
                                <div>
                                    <span class="block text-xs font-bold text-rose-500 uppercase tracking-wider">{{ __('Revoked By') }}</span>
                                    <span class="mt-0.5 block text-sm font-semibold text-slate-900">{{ $jitSession->revokedBy?->name ?? __('Unknown') }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold text-rose-500 uppercase tracking-wider">{{ __('Revoked At') }}</span>
                                    <span class="mt-0.5 block text-sm font-medium text-slate-800 font-mono">{{ $jitSession->revoked_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold text-rose-500 uppercase tracking-wider">{{ __('Revoke Reason') }}</span>
                                    <p class="mt-1 text-sm bg-rose-50 border border-rose-100 rounded-lg p-2.5 text-rose-800 font-semibold leading-relaxed">{{ $jitSession->revoke_reason }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Main credentials and connection instructions -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Request details info block -->
                <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-6 sm:p-8">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Justification / Business Reason') }}</h3>
                    <p class="text-sm text-slate-700 bg-slate-50 border border-slate-100 rounded-lg p-4 leading-relaxed whitespace-pre-line">{{ $jitSession->accessRequest->reason }}</p>
                </div>

                <!-- WinSCP / SFTP details card -->
                @if ($jitSession->isUsable())
                    @php
                        $sftpUsername = $jitSession->hasCreatedTemporaryCredential()
                            ? $jitSession->temporary_username
                            : $jitSession->targetServer->ssh_username;
                        $sftpSessionUrl = sprintf(
                            'sftp://%s@%s:%d/',
                            rawurlencode($sftpUsername),
                            $jitSession->targetServer->host,
                            $jitSession->targetServer->port
                        );
                    @endphp

                    <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-6 sm:p-8 space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 border-b border-slate-100 pb-5">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">{{ __('SFTP / WinSCP Connection Profile') }}</h3>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ __('Utilize these generated parameters in your secure client. This session is isolated and temporary.') }}
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-2 sm:shrink-0 sm:items-end">
                                @if ($jitSession->hasCreatedTemporaryCredential())
                                    <form method="POST" action="{{ route('sessions.temporary-credential.reveal', $jitSession) }}">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition">
                                            <svg class="mr-1.5 h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                            {{ __('Reveal Temporary Password') }}
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('sessions.sftp-profile.download', $jitSession) }}" class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-slate-800 transition">
                                    <svg class="mr-1.5 h-4 w-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                    {{ __('Download WinSCP Profile') }}
                                </a>
                            </div>
                        </div>

                        <!-- Temporary password display if revealed -->
                        @isset($temporaryPassword)
                            <div class="rounded-xl border border-amber-200 bg-amber-50/50 p-4 space-y-3 shadow-inner">
                                <div class="flex items-start">
                                    <svg class="h-5 w-5 text-amber-600 mr-2 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                                    </svg>
                                    <p class="text-sm font-semibold text-amber-900">
                                        {{ __('Temporary credential generated. This username and password will be automatically disabled/deleted when the session expires.') }}
                                    </p>
                                </div>
                                <div class="bg-white rounded-lg border border-amber-200 p-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <span class="block text-xxs font-bold text-amber-700 uppercase">{{ __('Temporary Password') }}</span>
                                        <input id="temporary-password" type="text" readonly value="{{ $temporaryPassword }}" class="mt-1 block w-full rounded-md border-0 bg-transparent py-0 text-sm font-semibold font-mono text-amber-950 focus:ring-0 truncate">
                                    </div>
                                    <button type="button" data-copy-target="temporary-password" class="inline-flex items-center justify-center rounded bg-amber-600 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-amber-700 transition">
                                        {{ __('Copy') }}
                                    </button>
                                </div>
                            </div>
                        @endisset

                        <!-- Connection credentials details grid -->
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">{{ __('Protocol') }}</dt>
                                <dd class="text-sm font-semibold text-slate-900 bg-slate-50 border border-slate-100 rounded-lg px-3.5 py-2.5">SFTP</dd>
                            </div>

                            <div>
                                <dt class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">{{ __('Target Host IP/DNS') }}</dt>
                                <dd class="flex rounded-lg shadow-sm border border-slate-200 overflow-hidden bg-slate-50">
                                    <input id="sftp-host" type="text" readonly value="{{ $jitSession->targetServer->host }}" class="block w-full border-0 bg-transparent text-sm font-semibold font-mono text-slate-800 focus:ring-0 py-2.5 px-3.5 truncate">
                                    <button type="button" data-copy-target="sftp-host" class="bg-white border-l border-slate-200 px-3.5 text-xs font-bold uppercase tracking-wider text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition">
                                        {{ __('Copy') }}
                                    </button>
                                </dd>
                            </div>

                            <div>
                                <dt class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">{{ __('Port') }}</dt>
                                <dd class="flex rounded-lg shadow-sm border border-slate-200 overflow-hidden bg-slate-50">
                                    <input id="sftp-port" type="text" readonly value="{{ $jitSession->targetServer->port }}" class="block w-full border-0 bg-transparent text-sm font-semibold font-mono text-slate-800 focus:ring-0 py-2.5 px-3.5 truncate">
                                    <button type="button" data-copy-target="sftp-port" class="bg-white border-l border-slate-200 px-3.5 text-xs font-bold uppercase tracking-wider text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition">
                                        {{ __('Copy') }}
                                    </button>
                                </dd>
                            </div>

                            <div>
                                <dt class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">{{ $jitSession->hasCreatedTemporaryCredential() ? __('Temporary Username') : __('SSH Username') }}</dt>
                                <dd class="flex rounded-lg shadow-sm border border-slate-200 overflow-hidden bg-slate-50">
                                    <input id="sftp-username" type="text" readonly value="{{ $sftpUsername }}" class="block w-full border-0 bg-transparent text-sm font-semibold font-mono text-slate-800 focus:ring-0 py-2.5 px-3.5 truncate">
                                    <button type="button" data-copy-target="sftp-username" class="bg-white border-l border-slate-200 px-3.5 text-xs font-bold uppercase tracking-wider text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition">
                                        {{ __('Copy') }}
                                    </button>
                                </dd>
                            </div>

                            <div class="sm:col-span-2">
                                <dt class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">{{ __('Session Connection URL') }}</dt>
                                <dd class="flex rounded-lg shadow-sm border border-slate-200 overflow-hidden bg-slate-50">
                                    <input id="sftp-session-url" type="text" readonly value="{{ $sftpSessionUrl }}" class="block w-full border-0 bg-transparent text-sm font-semibold font-mono text-slate-800 focus:ring-0 py-2.5 px-3.5 truncate">
                                    <button type="button" data-copy-target="sftp-session-url" class="bg-white border-l border-slate-200 px-3.5 text-xs font-bold uppercase tracking-wider text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition">
                                        {{ __('Copy') }}
                                    </button>
                                </dd>
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-200/60 bg-slate-50 p-4 text-xs text-slate-500 leading-relaxed">
                            @if ($jitSession->hasCreatedTemporaryCredential())
                                <span class="font-bold text-slate-700 block mb-1">{{ __('Security Policy Note') }}</span>
                                {{ __('The WinSCP profile template configuration file contains targets, hosts, and username formats only. Revealing the one-time password requires access to this web portal. SSH/SFTP endpoints reject connections once the JIT session monitor transitions status to Expired or Revoked.') }}
                            @else
                                <span class="font-bold text-slate-700 block mb-1">{{ __('Managed Key Credentials Note') }}</span>
                                {{ __('Your user session target credentials are safe and managed directly by the PAM database. Plain passwords or keys are injected securely and are not exposed. File transfer/SFTP access remains valid exclusively for the lifespan of this active session.') }}
                            @endif
                        </div>
                    </div>

                    <!-- Copy script functionality -->
                    <script>
                        document.querySelectorAll('[data-copy-target]').forEach((button) => {
                            button.addEventListener('click', async () => {
                                const input = document.getElementById(button.dataset.copyTarget);

                                if (! input) {
                                    return;
                                }

                                await navigator.clipboard.writeText(input.value);
                                const originalText = button.textContent;
                                button.textContent = @json(__('Copied'));

                                setTimeout(() => {
                                    button.textContent = originalText;
                                }, 1500);
                            });
                        });
                    </script>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
