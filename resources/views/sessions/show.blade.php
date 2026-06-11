<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('My Access') }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ __('JIT Session Console') }}</h2>
            </div>
            <div class="flex items-center gap-2 shrink-0 flex-wrap">
                <a href="{{ route('sessions.index') }}" class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    {{ __('My Sessions') }}
                </a>
                @if ($jitSession->isUsable() && $jitSession->targetServer->is_active)
                    <a href="{{ route('sessions.commands.index', $jitSession) }}" class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 transition">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5"/></svg>
                        {{ __('Open SSH Command') }}
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-5">

        {{-- ══════════════════════════════════════
             HERO / SESSION SUMMARY BANNER
        ══════════════════════════════════════ --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="border-l-4 {{ $jitSession->isUsable() ? 'border-emerald-500' : ($jitSession->revoked_at ? 'border-rose-500' : 'border-slate-400') }} px-6 py-5">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div class="flex items-start gap-4">
                        {{-- Server icon --}}
                        <div class="h-10 w-10 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0">
                            <svg class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3m3 3a3 3 0 1 0 6 0m-6 0H3m16.5 0a3 3 0 0 1-3 3m3-3a3 3 0 1 0-6 0m6 0h1.5m-1.5 0a3 3 0 0 1-3-3m0 0a3 3 0 0 1 3-3m-3 3h-1.5m-9-3a3 3 0 0 1 3-3m-3 3h-1.5" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-base font-bold text-slate-900">{{ $jitSession->targetServer->name }}</h3>
                                <x-badge :status="$jitSession->effectiveStatus()" />
                            </div>
                            <p class="mt-1 text-xs font-mono text-slate-500">{{ $jitSession->targetServer->host }}:{{ $jitSession->targetServer->port }}</p>
                        </div>
                    </div>
                    {{-- Usable indicator --}}
                    <div class="flex items-center gap-1.5 shrink-0 {{ $jitSession->isUsable() ? 'text-emerald-700' : 'text-slate-400' }}">
                        <span class="h-2 w-2 rounded-full {{ $jitSession->isUsable() ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                        <span class="text-xs font-semibold">{{ $jitSession->isUsable() ? __('Active & Usable') : __('Closed / Inactive') }}</span>
                    </div>
                </div>

                {{-- Stats row --}}
                <div class="mt-5 grid grid-cols-2 sm:grid-cols-4 gap-4 border-t border-slate-100 pt-4">
                    <div>
                        <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Started') }}</span>
                        <span class="mt-0.5 block text-xs font-mono text-slate-700">{{ $jitSession->started_at->timezone('Asia/Jakarta')->format('d M Y · H:i') }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Expires') }}</span>
                        <span class="mt-0.5 block text-xs font-mono {{ $jitSession->isUsable() ? 'text-amber-700 font-semibold' : 'text-slate-700' }}">{{ $jitSession->expires_at->timezone('Asia/Jakarta')->format('d M Y · H:i') }}</span>
                    </div>
                    @if ($jitSession->ended_at)
                    <div>
                        <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Ended') }}</span>
                        <span class="mt-0.5 block text-xs font-mono text-slate-700">{{ $jitSession->ended_at->timezone('Asia/Jakarta')->format('d M Y · H:i') }}</span>
                    </div>
                    @endif
                    @if ($jitSession->revoked_at)
                    <div>
                        <span class="block text-xs font-bold uppercase tracking-wider text-rose-500">{{ __('Revoked') }}</span>
                        <span class="mt-0.5 block text-xs font-mono text-rose-700">{{ $jitSession->revoked_at->timezone('Asia/Jakarta')->format('d M Y · H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════
             MAIN CONTENT GRID
        ══════════════════════════════════════ --}}
        <div class="grid gap-5 lg:grid-cols-3">

            {{-- LEFT COLUMN: Timeline + Credential Panel --}}
            <div class="lg:col-span-1 space-y-5">

                {{-- ACCESS TIMELINE --}}
                <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-5">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">{{ __('Session Lifecycle') }}</h4>
                    <ol class="relative border-l border-slate-200 space-y-5 ml-2">

                        {{-- Request submitted --}}
                        @if ($jitSession->accessRequest?->created_at)
                        <li class="ml-4">
                            <span class="absolute -left-1.5 mt-0.5 h-3 w-3 rounded-full border-2 border-white bg-indigo-400 ring-1 ring-indigo-300"></span>
                            <p class="text-xs font-bold text-slate-700">{{ __('Request Submitted') }}</p>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $jitSession->accessRequest->created_at->timezone('Asia/Jakarta')->format('d M Y · H:i') }}</p>
                        </li>
                        @endif

                        {{-- Approved --}}
                        @if ($jitSession->accessRequest?->approved_at)
                        <li class="ml-4">
                            <span class="absolute -left-1.5 mt-0.5 h-3 w-3 rounded-full border-2 border-white bg-blue-400 ring-1 ring-blue-300"></span>
                            <p class="text-xs font-bold text-slate-700">{{ __('Request Approved') }}</p>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $jitSession->accessRequest->approved_at->timezone('Asia/Jakarta')->format('d M Y · H:i') }}</p>
                            <p class="text-xs text-slate-400">{{ __('by') }} {{ $jitSession->accessRequest->approvedBy?->name ?? __('Admin') }}</p>
                        </li>
                        @endif

                        {{-- Session started --}}
                        <li class="ml-4">
                            <span class="absolute -left-1.5 mt-0.5 h-3 w-3 rounded-full border-2 border-white bg-emerald-400 ring-1 ring-emerald-300"></span>
                            <p class="text-xs font-bold text-slate-700">{{ __('Session Started') }}</p>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $jitSession->started_at->timezone('Asia/Jakarta')->format('d M Y · H:i') }}</p>
                        </li>

                        {{-- Temp credential created --}}
                        @if ($jitSession->temporary_credential_created_at)
                        <li class="ml-4">
                            <span class="absolute -left-1.5 mt-0.5 h-3 w-3 rounded-full border-2 border-white bg-amber-400 ring-1 ring-amber-300"></span>
                            <p class="text-xs font-bold text-slate-700">{{ __('Temp Credential Created') }}</p>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $jitSession->temporary_credential_created_at->timezone('Asia/Jakarta')->format('d M Y · H:i') }}</p>
                        </li>
                        @endif

                        {{-- Temp credential disabled --}}
                        @if ($jitSession->temporary_credential_disabled_at)
                        <li class="ml-4">
                            <span class="absolute -left-1.5 mt-0.5 h-3 w-3 rounded-full border-2 border-white bg-orange-400 ring-1 ring-orange-300"></span>
                            <p class="text-xs font-bold text-slate-700">{{ __('Credential Disabled') }}</p>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $jitSession->temporary_credential_disabled_at->timezone('Asia/Jakarta')->format('d M Y · H:i') }}</p>
                        </li>
                        @endif

                        {{-- Temp credential deleted --}}
                        @if ($jitSession->temporary_credential_deleted_at)
                        <li class="ml-4">
                            <span class="absolute -left-1.5 mt-0.5 h-3 w-3 rounded-full border-2 border-white bg-red-400 ring-1 ring-red-300"></span>
                            <p class="text-xs font-bold text-slate-700">{{ __('Credential Deleted') }}</p>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $jitSession->temporary_credential_deleted_at->timezone('Asia/Jakarta')->format('d M Y · H:i') }}</p>
                        </li>
                        @endif

                        {{-- Revoked --}}
                        @if ($jitSession->revoked_at)
                        <li class="ml-4">
                            <span class="absolute -left-1.5 mt-0.5 h-3 w-3 rounded-full border-2 border-white bg-rose-500 ring-1 ring-rose-400"></span>
                            <p class="text-xs font-bold text-rose-700">{{ __('Session Revoked') }}</p>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $jitSession->revoked_at->timezone('Asia/Jakarta')->format('d M Y · H:i') }}</p>
                            <p class="text-xs text-slate-400">{{ __('by') }} {{ $jitSession->revokedBy?->name ?? __('System') }}</p>
                        </li>
                        @else
                        {{-- Expires marker --}}
                        <li class="ml-4">
                            <span class="absolute -left-1.5 mt-0.5 h-3 w-3 rounded-full border-2 border-white {{ $jitSession->isUsable() ? 'bg-slate-300 ring-1 ring-slate-200' : 'bg-slate-500 ring-1 ring-slate-400' }}"></span>
                            <p class="text-xs font-bold {{ $jitSession->isUsable() ? 'text-slate-400' : 'text-slate-700' }}">{{ $jitSession->isUsable() ? __('Expires At (Scheduled)') : __('Session Expired') }}</p>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $jitSession->expires_at->timezone('Asia/Jakarta')->format('d M Y · H:i') }}</p>
                        </li>
                        @endif

                    </ol>
                </div>

                {{-- TEMPORARY CREDENTIAL PANEL --}}
                <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-5">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">{{ __('Credential Info') }}</h4>

                    @if ($jitSession->uses_temporary_credential)
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Type') }}</span>
                                <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-600/20">{{ __('Temporary') }}</span>
                            </div>
                            @if ($jitSession->temporary_username)
                            <div>
                                <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Username') }}</span>
                                <span class="mt-1 block font-mono text-sm font-semibold text-slate-800 bg-slate-50 border border-slate-200 rounded-md px-2.5 py-1">{{ $jitSession->temporary_username }}</span>
                            </div>
                            @endif
                            @if ($jitSession->temporary_credential_status)
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Status') }}</span>
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700 border border-slate-200 capitalize">{{ str_replace('_', ' ', $jitSession->temporary_credential_status) }}</span>
                            </div>
                            @endif
                            <p class="text-xs text-slate-400 border-t border-slate-100 pt-3 leading-relaxed">
                                <svg class="inline h-3.5 w-3.5 mr-1 text-amber-400 align-middle" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                                {{ __('This credential is temporary and expires with this JIT session.') }}
                            </p>
                        </div>
                    @else
                        <div class="rounded-lg border border-slate-100 bg-slate-50 p-4 text-xs text-slate-500 leading-relaxed">
                            <p class="font-semibold text-slate-600 mb-1">{{ __('Managed Credential') }}</p>
                            {{ __('This session uses the target server\'s configured credential behavior. Access is injected securely by PAM and is not exposed directly.') }}
                        </div>
                    @endif
                </div>

                {{-- SECURITY NOTE --}}
                <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Security Policy') }}</h4>
                    <ul class="space-y-1.5 text-xs text-slate-500 leading-relaxed">
                        <li class="flex items-start gap-1.5">
                            <svg class="h-3.5 w-3.5 text-slate-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                            {{ __('No SSH secrets or tokens are included in downloaded profile files or sent via email.') }}
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="h-3.5 w-3.5 text-slate-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            {{ __('Access is valid only while this JIT session is active.') }}
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="h-3.5 w-3.5 text-slate-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            {{ __('Expired or revoked sessions cannot be reused.') }}
                        </li>
                    </ul>
                </div>
            </div>

            {{-- RIGHT COLUMN: Main Actions + SFTP --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- ACCESS REASON --}}
                <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-5">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">{{ __('Justification / Business Reason') }}</h4>
                    <p class="text-sm text-slate-700 bg-slate-50 border border-slate-100 rounded-lg p-4 leading-relaxed whitespace-pre-line">{{ $jitSession->accessRequest->reason }}</p>
                </div>

                {{-- REVOCATION DETAIL (if revoked) --}}
                @if ($jitSession->revoked_at)
                <div class="rounded-xl border border-rose-200 bg-rose-50/50 p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="h-4 w-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-rose-600">{{ __('Session Revoked') }}</h4>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-3 text-xs mb-3">
                        <div>
                            <span class="font-bold text-rose-500 uppercase tracking-wider">{{ __('Revoked By') }}</span>
                            <p class="mt-0.5 font-semibold text-slate-800">{{ $jitSession->revokedBy?->name ?? __('Unknown') }}</p>
                        </div>
                        <div>
                            <span class="font-bold text-rose-500 uppercase tracking-wider">{{ __('Revoked At') }}</span>
                            <p class="mt-0.5 font-mono text-slate-700">{{ $jitSession->revoked_at->timezone('Asia/Jakarta')->format('d M Y · H:i') }}</p>
                        </div>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-rose-500 uppercase tracking-wider block mb-1">{{ __('Revoke Reason') }}</span>
                        <p class="text-sm bg-white border border-rose-200 rounded-lg p-3 text-rose-800 font-semibold leading-relaxed">{{ $jitSession->revoke_reason }}</p>
                    </div>
                </div>
                @endif

                {{-- SFTP / WINSCP CONNECTION PANEL --}}
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

                    <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        {{-- Card header with actions --}}
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 px-5 py-4 bg-slate-50/30">
                            <div>
                                <h4 class="text-sm font-bold text-slate-900">{{ __('SFTP / WinSCP Connection Profile') }}</h4>
                                <p class="text-xs text-slate-500 mt-0.5">{{ __('Use these parameters in your SFTP client. Session-isolated and temporary.') }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0 flex-wrap">
                                @if ($jitSession->hasCreatedTemporaryCredential())
                                    <form method="POST" action="{{ route('sessions.temporary-credential.reveal', $jitSession) }}">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-md border border-amber-300 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100 transition">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                            {{ __('Reveal Password') }}
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('sessions.sftp-profile.download', $jitSession) }}" class="inline-flex items-center gap-1.5 rounded-md bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800 transition">
                                    <svg class="h-3.5 w-3.5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                    {{ __('Download WinSCP Profile') }}
                                </a>
                            </div>
                        </div>

                        <div class="p-5 space-y-5">
                            {{-- Revealed temporary password --}}
                            @isset($temporaryPassword)
                                <div class="rounded-lg border border-amber-200 bg-amber-50/60 p-4">
                                    <div class="flex items-center gap-2 mb-3">
                                        <svg class="h-4 w-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                                        <p class="text-xs font-semibold text-amber-900">{{ __('Temporary credential revealed. This will be auto-disabled when the session expires.') }}</p>
                                    </div>
                                    <div class="flex items-stretch rounded-lg border border-amber-300 overflow-hidden bg-white shadow-sm">
                                        <div class="flex-1 px-3 py-2 min-w-0">
                                            <span class="block text-xxs font-bold text-amber-600 uppercase tracking-wider">{{ __('Temporary Password') }}</span>
                                            <input id="temporary-password" type="text" readonly value="{{ $temporaryPassword }}" class="block w-full border-0 bg-transparent py-0.5 text-sm font-semibold font-mono text-amber-950 focus:ring-0 truncate">
                                        </div>
                                        <button type="button" data-copy-target="temporary-password" class="shrink-0 bg-amber-600 hover:bg-amber-700 text-white px-4 text-xs font-bold uppercase tracking-wide transition">
                                            {{ __('Copy') }}
                                        </button>
                                    </div>
                                </div>
                            @endisset

                            {{-- Connection fields grid --}}
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">{{ __('Protocol') }}</dt>
                                    <dd class="text-sm font-semibold text-slate-900 bg-slate-50 border border-slate-200 rounded-md px-3 py-2">SFTP</dd>
                                </div>

                                <div>
                                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">{{ __('Host') }}</dt>
                                    <dd class="flex rounded-md border border-slate-200 overflow-hidden bg-slate-50">
                                        <input id="sftp-host" type="text" readonly value="{{ $jitSession->targetServer->host }}" class="block w-full border-0 bg-transparent text-sm font-semibold font-mono text-slate-800 focus:ring-0 py-2 px-3 truncate">
                                        <button type="button" data-copy-target="sftp-host" class="shrink-0 border-l border-slate-200 bg-white px-3 text-xs font-bold text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition">{{ __('Copy') }}</button>
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">{{ __('Port') }}</dt>
                                    <dd class="flex rounded-md border border-slate-200 overflow-hidden bg-slate-50">
                                        <input id="sftp-port" type="text" readonly value="{{ $jitSession->targetServer->port }}" class="block w-full border-0 bg-transparent text-sm font-semibold font-mono text-slate-800 focus:ring-0 py-2 px-3 truncate">
                                        <button type="button" data-copy-target="sftp-port" class="shrink-0 border-l border-slate-200 bg-white px-3 text-xs font-bold text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition">{{ __('Copy') }}</button>
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">{{ $jitSession->hasCreatedTemporaryCredential() ? __('Temporary Username') : __('SSH Username') }}</dt>
                                    <dd class="flex rounded-md border border-slate-200 overflow-hidden bg-slate-50">
                                        <input id="sftp-username" type="text" readonly value="{{ $sftpUsername }}" class="block w-full border-0 bg-transparent text-sm font-semibold font-mono text-slate-800 focus:ring-0 py-2 px-3 truncate">
                                        <button type="button" data-copy-target="sftp-username" class="shrink-0 border-l border-slate-200 bg-white px-3 text-xs font-bold text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition">{{ __('Copy') }}</button>
                                    </dd>
                                </div>

                                <div class="sm:col-span-2">
                                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">{{ __('Session URL') }}</dt>
                                    <dd class="flex rounded-md border border-slate-200 overflow-hidden bg-slate-50">
                                        <input id="sftp-session-url" type="text" readonly value="{{ $sftpSessionUrl }}" class="block w-full border-0 bg-transparent text-sm font-semibold font-mono text-slate-800 focus:ring-0 py-2 px-3 truncate">
                                        <button type="button" data-copy-target="sftp-session-url" class="shrink-0 border-l border-slate-200 bg-white px-3 text-xs font-bold text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition">{{ __('Copy') }}</button>
                                    </dd>
                                </div>
                            </div>

                            {{-- WinSCP download note --}}
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-xs text-slate-500 leading-relaxed">
                                @if ($jitSession->hasCreatedTemporaryCredential())
                                    <span class="font-bold text-slate-700 block mb-1">{{ __('Profile does not include password') }}</span>
                                    {{ __('The WinSCP profile file contains host, port, and username only. The temporary password must be revealed from this page and entered manually in your client.') }}
                                @else
                                    <span class="font-bold text-slate-700 block mb-1">{{ __('Managed Key Credential') }}</span>
                                    {{ __('Credentials are managed securely by PAM. SFTP/SSH access is valid exclusively for the lifespan of this active session.') }}
                                @endif
                            </div>

                            {{-- Copy script --}}
                            <script>
                                document.querySelectorAll('[data-copy-target]').forEach((button) => {
                                    button.addEventListener('click', async () => {
                                        const input = document.getElementById(button.dataset.copyTarget);
                                        if (! input) return;
                                        await navigator.clipboard.writeText(input.value);
                                        const originalText = button.textContent;
                                        button.textContent = @json(__('Copied'));
                                        setTimeout(() => { button.textContent = originalText; }, 1500);
                                    });
                                });
                            </script>
                        </div>
                    </div>
                @endif

            </div>{{-- end right column --}}
        </div>{{-- end grid --}}
    </div>
</x-app-layout>
