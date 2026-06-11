<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Administration') }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                    {{ __('JIT Session Details') }}
                </h2>
            </div>

            <a href="{{ route('admin.sessions.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-900 transition">
                {{ __('Back to JIT Sessions') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        <div class="grid gap-8 lg:grid-cols-3">
            <!-- Sidebar Panel: Session Info -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-6">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Status') }}</span>
                        <x-badge :status="$jitSession->effectiveStatus()" />
                    </div>

                    <div class="space-y-4">
                        <div>
                            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('User') }}</span>
                            <span class="mt-1 block text-sm font-semibold text-slate-900">{{ $jitSession->user->name }}</span>
                            <span class="text-xs text-slate-400 block">{{ $jitSession->user->email }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Target Server') }}</span>
                            <span class="mt-1 block text-sm font-semibold text-slate-900">{{ $jitSession->targetServer->name }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Host IP') }}</span>
                            <span class="mt-1 block text-sm font-mono text-slate-600">{{ $jitSession->targetServer->host }}:{{ $jitSession->targetServer->port }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-6">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-4">{{ __('Session Timeline') }}</h3>
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
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Main Panel: Detailed Lifecycle and Revoke Actions -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Request justification -->
                <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-6 sm:p-8">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Justification / Business Reason') }}</h3>
                    <p class="text-sm text-slate-700 bg-slate-50 border border-slate-100 rounded-lg p-4 leading-relaxed whitespace-pre-line">{{ $jitSession->accessRequest->reason }}</p>
                    
                    @if ($jitSession->revoked_at)
                        <div class="mt-4">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-rose-500 mb-2">{{ __('Revocation Reason') }}</h3>
                            <p class="text-sm text-rose-800 bg-rose-50 border border-rose-100 rounded-lg p-4 leading-relaxed whitespace-pre-line">{{ $jitSession->revoke_reason }}</p>
                        </div>
                    @endif
                </div>

                <!-- Temporary linux user account telemetry state -->
                <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-6 sm:p-8">
                    <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-4 mb-5">{{ __('Temporary Credential Lifecycle') }}</h3>

                    <dl class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Uses Temporary Credential') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $jitSession->uses_temporary_credential ? __('Yes') : __('No') }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Temporary Username') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-800 font-mono bg-slate-50 border border-slate-100 rounded px-2 py-0.5 inline-block">{{ $jitSession->temporary_username ?? __('None') }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Credential Status') }}</dt>
                            <dd class="mt-1 text-sm">
                                @if($jitSession->temporary_credential_status)
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-800 capitalize border border-slate-200">{{ str_replace('_', ' ', $jitSession->temporary_credential_status) }}</span>
                                @else
                                    <span class="text-slate-400 font-medium">{{ __('None') }}</span>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Created At') }}</dt>
                            <dd class="mt-1 text-sm font-medium text-slate-700 font-mono">{{ $jitSession->temporary_credential_created_at?->timezone('Asia/Jakarta')->format('Y-m-d H:i') ?? __('None') }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Disabled At') }}</dt>
                            <dd class="mt-1 text-sm font-medium text-slate-700 font-mono">{{ $jitSession->temporary_credential_disabled_at?->timezone('Asia/Jakarta')->format('Y-m-d H:i') ?? __('None') }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Deleted At') }}</dt>
                            <dd class="mt-1 text-sm font-medium text-slate-700 font-mono">{{ $jitSession->temporary_credential_deleted_at?->timezone('Asia/Jakarta')->format('Y-m-d H:i') ?? __('None') }}</dd>
                        </div>

                        @if ($jitSession->temporary_credential_error)
                            <div class="sm:col-span-2 bg-red-50 border border-red-100 p-4 rounded-xl">
                                <dt class="text-xs font-bold uppercase tracking-wider text-red-800">{{ __('Safe Error Details') }}</dt>
                                <dd class="mt-1 text-sm text-red-700 font-medium whitespace-pre-line leading-relaxed">{{ $jitSession->temporary_credential_error }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <!-- Revoke form option -->
                @if ($jitSession->isActive())
                    <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-6 sm:p-8">
                        <h3 class="text-lg font-bold text-slate-900">{{ __('Revoke Active Session') }}</h3>
                        <p class="text-sm text-slate-500 mt-1 mb-4">{{ __('Forcibly terminate target user process execution and revoke SFTP / SSH terminal tokens instantly.') }}</p>

                        <form method="POST" action="{{ route('admin.sessions.revoke', $jitSession) }}" class="space-y-4">
                            @csrf

                            <div>
                                <x-input-label for="revoke_reason" :value="__('Revocation Reason')" class="font-bold text-slate-700" />
                                <textarea id="revoke_reason" name="revoke_reason" rows="4" class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/20" placeholder="e.g. Completed operations early, security policy violation detected, etc." required>{{ old('revoke_reason') }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('revoke_reason')" />
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500 transition focus:outline-none">
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    {{ __('Revoke Access') }}
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
