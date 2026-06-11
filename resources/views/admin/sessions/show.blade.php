<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Administration') }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ __('JIT Session Details') }}</h2>
            </div>
            <a href="{{ route('admin.sessions.index') }}" class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shrink-0">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                {{ __('JIT Sessions') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6 space-y-5">

        {{-- ══════════════════════════════════════
             OPERATOR HERO BANNER
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
                    {{-- Requester identity --}}
                    <div class="shrink-0 rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 text-right">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Requester') }}</p>
                        <p class="text-sm font-semibold text-slate-900 mt-0.5">{{ $jitSession->user->name }}</p>
                        <p class="text-xs text-slate-400">{{ $jitSession->user->email }}</p>
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
                    {{-- Session usable status --}}
                    <div>
                        <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Usable') }}</span>
                        <span class="mt-1 inline-flex items-center gap-1 text-xs font-semibold {{ $jitSession->isUsable() ? 'text-emerald-700' : 'text-slate-400' }}">
                            <span class="h-2 w-2 rounded-full {{ $jitSession->isUsable() ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                            {{ $jitSession->isUsable() ? __('Yes') : __('No') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════
             MAIN CONTENT GRID
        ══════════════════════════════════════ --}}
        <div class="grid gap-5 lg:grid-cols-3">

            {{-- LEFT: Timeline + Session Info --}}
            <div class="lg:col-span-1 space-y-5">

                {{-- ACCESS TIMELINE --}}
                <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-5">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">{{ __('Session Lifecycle') }}</h4>
                    <ol class="relative border-l border-slate-200 space-y-5 ml-2">

                        @if ($jitSession->accessRequest?->created_at)
                        <li class="ml-4">
                            <span class="absolute -left-1.5 mt-0.5 h-3 w-3 rounded-full border-2 border-white bg-indigo-400 ring-1 ring-indigo-300"></span>
                            <p class="text-xs font-bold text-slate-700">{{ __('Request Submitted') }}</p>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $jitSession->accessRequest->created_at->timezone('Asia/Jakarta')->format('d M Y · H:i') }}</p>
                        </li>
                        @endif

                        @if ($jitSession->accessRequest?->approved_at)
                        <li class="ml-4">
                            <span class="absolute -left-1.5 mt-0.5 h-3 w-3 rounded-full border-2 border-white bg-blue-400 ring-1 ring-blue-300"></span>
                            <p class="text-xs font-bold text-slate-700">{{ __('Request Approved') }}</p>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $jitSession->accessRequest->approved_at->timezone('Asia/Jakarta')->format('d M Y · H:i') }}</p>
                            <p class="text-xs text-slate-400">{{ $jitSession->accessRequest->approvedBy?->name ?? __('Admin') }}</p>
                        </li>
                        @endif

                        <li class="ml-4">
                            <span class="absolute -left-1.5 mt-0.5 h-3 w-3 rounded-full border-2 border-white bg-emerald-400 ring-1 ring-emerald-300"></span>
                            <p class="text-xs font-bold text-slate-700">{{ __('Session Started') }}</p>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $jitSession->started_at->timezone('Asia/Jakarta')->format('d M Y · H:i') }}</p>
                        </li>

                        @if ($jitSession->temporary_credential_created_at)
                        <li class="ml-4">
                            <span class="absolute -left-1.5 mt-0.5 h-3 w-3 rounded-full border-2 border-white bg-amber-400 ring-1 ring-amber-300"></span>
                            <p class="text-xs font-bold text-slate-700">{{ __('Temp Credential Created') }}</p>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $jitSession->temporary_credential_created_at->timezone('Asia/Jakarta')->format('d M Y · H:i') }}</p>
                        </li>
                        @endif

                        @if ($jitSession->temporary_credential_disabled_at)
                        <li class="ml-4">
                            <span class="absolute -left-1.5 mt-0.5 h-3 w-3 rounded-full border-2 border-white bg-orange-400 ring-1 ring-orange-300"></span>
                            <p class="text-xs font-bold text-slate-700">{{ __('Credential Disabled') }}</p>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $jitSession->temporary_credential_disabled_at->timezone('Asia/Jakarta')->format('d M Y · H:i') }}</p>
                        </li>
                        @endif

                        @if ($jitSession->temporary_credential_deleted_at)
                        <li class="ml-4">
                            <span class="absolute -left-1.5 mt-0.5 h-3 w-3 rounded-full border-2 border-white bg-red-400 ring-1 ring-red-300"></span>
                            <p class="text-xs font-bold text-slate-700">{{ __('Credential Deleted') }}</p>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $jitSession->temporary_credential_deleted_at->timezone('Asia/Jakarta')->format('d M Y · H:i') }}</p>
                        </li>
                        @endif

                        @if ($jitSession->revoked_at)
                        <li class="ml-4">
                            <span class="absolute -left-1.5 mt-0.5 h-3 w-3 rounded-full border-2 border-white bg-rose-500 ring-1 ring-rose-400"></span>
                            <p class="text-xs font-bold text-rose-700">{{ __('Session Revoked') }}</p>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $jitSession->revoked_at->timezone('Asia/Jakarta')->format('d M Y · H:i') }}</p>
                            <p class="text-xs text-slate-400">{{ $jitSession->revokedBy?->name ?? __('System') }}</p>
                        </li>
                        @else
                        <li class="ml-4">
                            <span class="absolute -left-1.5 mt-0.5 h-3 w-3 rounded-full border-2 border-white {{ $jitSession->isUsable() ? 'bg-slate-300 ring-1 ring-slate-200' : 'bg-slate-500 ring-1 ring-slate-400' }}"></span>
                            <p class="text-xs font-bold {{ $jitSession->isUsable() ? 'text-slate-400' : 'text-slate-700' }}">{{ $jitSession->isUsable() ? __('Expires At (Scheduled)') : __('Session Expired') }}</p>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $jitSession->expires_at->timezone('Asia/Jakarta')->format('d M Y · H:i') }}</p>
                        </li>
                        @endif

                    </ol>
                </div>

                {{-- SECURITY NOTES --}}
                <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Security Policy') }}</h4>
                    <ul class="space-y-1.5 text-xs text-slate-500 leading-relaxed">
                        <li class="flex items-start gap-1.5">
                            <svg class="h-3.5 w-3.5 text-slate-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                            {{ __('No SSH secrets or Proxmox tokens are included in downloaded files or emails.') }}
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="h-3.5 w-3.5 text-slate-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            {{ __('Access is valid only while the JIT session is active.') }}
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="h-3.5 w-3.5 text-slate-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            {{ __('Expired or revoked sessions cannot be reused.') }}
                        </li>
                    </ul>
                </div>
            </div>

            {{-- RIGHT: Main panel --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- JUSTIFICATION --}}
                <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-5">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">{{ __('Access Justification') }}</h4>
                    <p class="text-sm text-slate-700 bg-slate-50 border border-slate-100 rounded-lg p-4 leading-relaxed whitespace-pre-line">{{ $jitSession->accessRequest->reason }}</p>

                    @if ($jitSession->revoked_at)
                    <div class="mt-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-rose-500 mb-2">{{ __('Revocation Reason') }}</h4>
                        <p class="text-sm text-rose-800 bg-rose-50 border border-rose-100 rounded-lg p-4 leading-relaxed whitespace-pre-line">{{ $jitSession->revoke_reason }}</p>
                    </div>
                    @endif
                </div>

                {{-- TEMPORARY CREDENTIAL LIFECYCLE --}}
                <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
                        <h4 class="text-sm font-bold text-slate-900">{{ __('Temporary Credential Lifecycle') }}</h4>
                        @if ($jitSession->uses_temporary_credential)
                            <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-600/20">{{ __('Temporary') }}</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600 ring-1 ring-slate-300/40">{{ __('Managed / Static') }}</span>
                        @endif
                    </div>

                    <dl class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Uses Temporary Credential') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $jitSession->uses_temporary_credential ? __('Yes') : __('No') }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Temporary Username') }}</dt>
                            <dd class="mt-1">
                                @if ($jitSession->temporary_username)
                                    <span class="font-mono text-sm font-semibold text-slate-800 bg-slate-50 border border-slate-200 rounded px-2.5 py-0.5 inline-block">{{ $jitSession->temporary_username }}</span>
                                @else
                                    <span class="text-sm text-slate-400">{{ __('None') }}</span>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Credential Status') }}</dt>
                            <dd class="mt-1">
                                @if ($jitSession->temporary_credential_status)
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-800 capitalize border border-slate-200">{{ str_replace('_', ' ', $jitSession->temporary_credential_status) }}</span>
                                @else
                                    <span class="text-sm text-slate-400">{{ __('None') }}</span>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Credential Created') }}</dt>
                            <dd class="mt-1 text-sm font-mono text-slate-700">{{ $jitSession->temporary_credential_created_at?->timezone('Asia/Jakarta')->format('d M Y · H:i') ?? __('—') }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Disabled At') }}</dt>
                            <dd class="mt-1 text-sm font-mono text-slate-700">{{ $jitSession->temporary_credential_disabled_at?->timezone('Asia/Jakarta')->format('d M Y · H:i') ?? __('—') }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Deleted At') }}</dt>
                            <dd class="mt-1 text-sm font-mono text-slate-700">{{ $jitSession->temporary_credential_deleted_at?->timezone('Asia/Jakarta')->format('d M Y · H:i') ?? __('—') }}</dd>
                        </div>

                        @if ($jitSession->temporary_credential_error)
                        <div class="sm:col-span-2 rounded-lg border border-red-200 bg-red-50 p-4">
                            <dt class="text-xs font-bold uppercase tracking-wider text-red-700 mb-1">{{ __('Safe Error Details') }}</dt>
                            <dd class="text-sm text-red-700 font-medium whitespace-pre-line leading-relaxed">{{ $jitSession->temporary_credential_error }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                {{-- REVOKE ACTION (operator view – requires deliberate textarea fill) --}}
                @if ($jitSession->isActive())
                <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="border-l-4 border-rose-500 px-5 py-4 bg-rose-50/30">
                        <div class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-rose-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                            <div>
                                <h4 class="text-sm font-bold text-slate-900">{{ __('Revoke Active Session') }}</h4>
                                <p class="text-xs text-slate-500 mt-0.5">{{ __('Forcibly terminates the session and invalidates all credentials immediately. This action cannot be undone.') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-5">
                        <form method="POST" action="{{ route('admin.sessions.revoke', $jitSession) }}" class="space-y-4">
                            @csrf
                            <div>
                                <x-input-label for="revoke_reason" :value="__('Revocation Reason')" class="font-bold text-slate-700 text-xs uppercase tracking-wider" />
                                <textarea id="revoke_reason" name="revoke_reason" rows="3" class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-rose-500 focus:ring-rose-500/20 text-sm" placeholder="{{ __('e.g. Operations completed, policy violation, emergency lockout...') }}" required>{{ old('revoke_reason') }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('revoke_reason')" />
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-red-700 bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 transition focus:outline-none">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    {{ __('Revoke Access Now') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

            </div>{{-- end right column --}}
        </div>{{-- end grid --}}
    </div>
</x-app-layout>
