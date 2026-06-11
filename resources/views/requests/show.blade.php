<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('My Access') }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                    {{ __('Request Details') }}
                </h2>
            </div>

            <a href="{{ route('requests.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-900 transition">
                {{ __('Back to My Requests') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-6 sm:p-8">
                <div class="flex items-center justify-between border-b border-slate-100 pb-6 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">#{{ $accessRequest->id }}: {{ $accessRequest->targetServer->name }}</h3>
                        <p class="text-sm text-slate-500 mt-1">{{ __('Request lifecycle and approval details.') }}</p>
                    </div>
                    <x-badge :status="$accessRequest->effectiveStatus()" />
                </div>

                <dl class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Target Server') }}</dt>
                        <dd class="mt-1.5 text-sm font-semibold text-slate-900">{{ $accessRequest->targetServer->name }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Connection Host') }}</dt>
                        <dd class="mt-1.5 text-sm font-mono text-slate-600">{{ $accessRequest->targetServer->host }}:{{ $accessRequest->targetServer->port }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Access Duration') }}</dt>
                        <dd class="mt-1.5 text-sm font-semibold text-slate-900">{{ $accessRequest->formattedDuration() }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Status') }}</dt>
                        <dd class="mt-1.5 text-sm font-semibold text-slate-900 capitalize">{{ $accessRequest->effectiveStatus() }}</dd>
                    </div>

                    <div class="sm:col-span-2 bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('Access Reason') }}</dt>
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
                                <dd class="mt-1.5 text-sm font-semibold text-slate-900">{{ $accessRequest->approved_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</dd>
                            </div>
                        </div>
                    @endif

                    <!-- Rejection metadata -->
                    @if ($accessRequest->rejected_at)
                        <div class="sm:col-span-2 border-t border-slate-100 pt-6 grid gap-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Rejected By') }}</dt>
                                <dd class="mt-1.5 text-sm font-semibold text-slate-900 text-rose-700">{{ $accessRequest->rejectedBy?->name ?? __('Unknown') }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Rejected At') }}</dt>
                                <dd class="mt-1.5 text-sm font-semibold text-slate-900 text-rose-700">{{ $accessRequest->rejected_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</dd>
                            </div>

                            <div class="sm:col-span-2 bg-red-50 p-4 rounded-xl border border-red-100">
                                <dt class="text-xs font-bold uppercase tracking-wider text-red-800">{{ __('Rejection Reason') }}</dt>
                                <dd class="mt-2 text-sm text-red-700 whitespace-pre-line leading-relaxed font-semibold">{{ $accessRequest->rejection_reason }}</dd>
                            </div>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
