<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Review Access Request') }}
            </h2>

            <a href="{{ route('admin.access-requests.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                {{ __('Back to Access Requests') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="space-y-6">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <dl class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Requester') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $accessRequest->user->name }} &lt;{{ $accessRequest->user->email }}&gt;</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Status') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($accessRequest->effectiveStatus()) }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Target Server') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $accessRequest->targetServer->name }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Host') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $accessRequest->targetServer->host }}:{{ $accessRequest->targetServer->port }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Duration') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $accessRequest->requested_duration_minutes }} {{ __('minutes') }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Submitted') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $accessRequest->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</dd>
                        </div>

                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">{{ __('Reason') }}</dt>
                            <dd class="mt-1 whitespace-pre-line text-sm text-gray-900">{{ $accessRequest->reason }}</dd>
                        </div>

                        @if ($accessRequest->approved_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('Approved By') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $accessRequest->approvedBy?->name ?? __('Unknown') }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('Approved At') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $accessRequest->approved_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</dd>
                            </div>
                        @endif

                        @if ($accessRequest->rejected_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('Rejected By') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $accessRequest->rejectedBy?->name ?? __('Unknown') }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('Rejected At') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $accessRequest->rejected_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}</dd>
                            </div>

                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">{{ __('Rejection Reason') }}</dt>
                                <dd class="mt-1 whitespace-pre-line text-sm text-gray-900">{{ $accessRequest->rejection_reason }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                @if ($accessRequest->isPending())
                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Approve Request') }}</h3>
                            <p class="mt-1 text-sm text-gray-600">{{ __('Approving records your admin account and timestamp.') }}</p>

                            <form method="POST" action="{{ route('admin.access-requests.approve', $accessRequest) }}" class="mt-6">
                                @csrf

                                <x-primary-button>
                                    {{ __('Approve') }}
                                </x-primary-button>
                            </form>
                        </div>

                        <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Reject Request') }}</h3>

                            <form method="POST" action="{{ route('admin.access-requests.reject', $accessRequest) }}" class="mt-4 space-y-4">
                                @csrf

                                <div>
                                    <x-input-label for="rejection_reason" :value="__('Rejection Reason')" />
                                    <textarea id="rejection_reason" name="rejection_reason" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('rejection_reason') }}</textarea>
                                    <x-input-error class="mt-2" :messages="$errors->get('rejection_reason')" />
                                </div>

                                <x-danger-button>
                                    {{ __('Reject') }}
                                </x-danger-button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
