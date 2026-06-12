<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Administration') }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                    {{ __('Review Access Request') }}
                </h2>
            </div>

            <a href="{{ route('admin.access-requests.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-900 transition">
                {{ __('Back to Access Requests') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Request details card -->
            <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-6 sm:p-8">
                <div class="flex items-center justify-between border-b border-slate-100 pb-6 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">{{ __('Request Details') }}</h3>
                        <p class="text-sm text-slate-500 mt-1">{{ __('Review user information and target server requirements.') }}</p>
                    </div>
                    <x-badge :status="$accessRequest->effectiveStatus()" />
                </div>

                <dl class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Requester') }}</dt>
                        <dd class="mt-1.5 text-sm font-semibold text-slate-900">
                            {{ $accessRequest->user->name }} 
                            <span class="text-xs font-medium text-slate-400">&lt;{{ $accessRequest->user->email }}&gt;</span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Target Server') }}</dt>
                        <dd class="mt-1.5 text-sm font-semibold text-slate-900">{{ $accessRequest->targetServer->name }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Connection Host') }}</dt>
                        <dd class="mt-1.5 text-sm font-mono text-slate-600">{{ $accessRequest->targetServer->host }}:{{ $accessRequest->targetServer->port }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Server Health') }}</dt>
                        <dd class="mt-1.5">
                            @if ($accessRequest->targetServer->last_health_status)
                                <div class="flex flex-col gap-1">
                                    <x-badge :status="$accessRequest->targetServer->healthStatusBadgeVariant()">
                                        {{ $accessRequest->targetServer->healthStatusLabel() }}
                                    </x-badge>
                                    <span class="text-xs text-slate-400 font-mono">
                                        {{ $accessRequest->targetServer->last_health_checked_at?->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}
                                    </span>
                                    @if (in_array($accessRequest->targetServer->last_health_status, ['tcp_failed', 'ssh_failed', 'unreachable', 'error'], true))
                                        <p class="mt-1 text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded px-2 py-1">
                                            ⚠ {{ __('Server may be unreachable. Approval is still possible but consider running a health check first.') }}
                                        </p>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-slate-400 italic">{{ __('Health not checked') }}</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('JIT Readiness') }}</dt>
                        <dd class="mt-1.5">
                            @if ($accessRequest->targetServer->last_jit_readiness_status)
                                <div class="flex flex-col gap-1">
                                    <x-badge :status="$accessRequest->targetServer->jitReadinessBadgeVariant()">
                                        {{ $accessRequest->targetServer->jitReadinessStatusLabel() }}
                                    </x-badge>
                                    <span class="text-xs text-slate-400 font-mono">
                                        {{ $accessRequest->targetServer->last_jit_readiness_checked_at?->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}
                                    </span>
                                    @if (in_array($accessRequest->targetServer->last_jit_readiness_status, ['not_ready', 'ssh_failed', 'error'], true))
                                        <p class="mt-1 text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded px-2 py-1">
                                            ⚠ {{ __('Temporary credential approval may fail until sudo user management permissions are configured.') }}
                                        </p>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-slate-400 italic">{{ __('Readiness not checked') }}</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Requested Duration') }}</dt>
                        <dd class="mt-1.5 text-sm font-semibold text-slate-900">{{ $accessRequest->formattedDuration() }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Submitted') }}</dt>
                        <dd class="mt-1.5 text-sm font-medium text-slate-500 font-mono">{{ $accessRequest->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Usability State') }}</dt>
                        <dd class="mt-1.5 text-sm">
                            @if($accessRequest->isPending())
                                <span class="text-amber-600 font-semibold text-sm">{{ __('Awaiting Decision') }}</span>
                            @else
                                <span class="text-slate-600 font-semibold text-sm">{{ ucfirst($accessRequest->effectiveStatus()) }}</span>
                            @endif
                        </dd>
                    </div>

                    <div class="sm:col-span-2 bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-505 text-slate-500">{{ __('Access Reason / Business Case') }}</dt>
                        <dd class="mt-2 text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ $accessRequest->reason }}</dd>
                    </div>

                    <!-- Approval metadata -->
                    @if ($accessRequest->approved_at)
                        <div class="sm:col-span-2 border-t border-slate-100 pt-6 grid gap-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Approved By') }}</dt>
                                <dd class="mt-1.5 text-sm font-semibold text-slate-900">{{ $accessRequest->approvedBy?->name ?? __('Unknown') }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Approved At') }}</dt>
                                <dd class="mt-1.5 text-sm font-medium text-slate-600 font-mono">{{ $accessRequest->approved_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</dd>
                            </div>
                        </div>
                    @endif

                    <!-- Rejection metadata -->
                    @if ($accessRequest->rejected_at)
                        <div class="sm:col-span-2 border-t border-slate-100 pt-6 grid gap-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Rejected By') }}</dt>
                                <dd class="mt-1.5 text-sm font-semibold text-rose-700">{{ $accessRequest->rejectedBy?->name ?? __('Unknown') }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Rejected At') }}</dt>
                                <dd class="mt-1.5 text-sm font-medium text-rose-600 font-mono">{{ $accessRequest->rejected_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</dd>
                            </div>

                            <div class="sm:col-span-2 bg-red-50 p-4 rounded-xl border border-red-100">
                                <dt class="text-xs font-bold uppercase tracking-wider text-red-800">{{ __('Rejection Reason') }}</dt>
                                <dd class="mt-2 text-sm text-red-700 whitespace-pre-line leading-relaxed font-semibold">{{ $accessRequest->rejection_reason }}</dd>
                            </div>
                        </div>
                    @endif
                </dl>
            </div>

            <!-- Review Actions Card -->
            @if ($accessRequest->isPending())
                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Approve Pane -->
                    <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-6 flex flex-col justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">{{ __('Approve Request') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ __('This will immediately create an active time-limited JIT session and provision target server credentials.') }}</p>
                        </div>

                        <form method="POST" action="{{ route('admin.access-requests.approve', $accessRequest) }}" class="mt-6">
                            @csrf
                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition focus:outline-none">
                                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                                {{ __('Approve & Provision') }}
                            </button>
                        </form>
                    </div>

                    <!-- Reject Pane -->
                    <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-6">
                        <h3 class="text-lg font-bold text-slate-900">{{ __('Reject Request') }}</h3>
                        <p class="mt-1 text-sm text-slate-500 mb-4">{{ __('Provide a clear reason explaining why this access request is declined.') }}</p>

                        <form method="POST" action="{{ route('admin.access-requests.reject', $accessRequest) }}" class="space-y-4">
                            @csrf

                            <div>
                                <x-input-label for="rejection_reason" :value="__('Rejection Reason')" class="font-bold text-slate-700" />
                                <textarea id="rejection_reason" name="rejection_reason" rows="4" class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/20" placeholder="e.g. Invalid request details, target server maintenance scheduled, etc." required>{{ old('rejection_reason') }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('rejection_reason')" />
                            </div>

                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500 transition focus:outline-none">
                                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                                {{ __('Reject Request') }}
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
