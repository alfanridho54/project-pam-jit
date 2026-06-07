<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Access Request') }}
            </h2>

            <a href="{{ route('requests.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                {{ __('Back to My Requests') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <dl class="grid gap-6 sm:grid-cols-2">
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
                        <dt class="text-sm font-medium text-gray-500">{{ __('Status') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($accessRequest->effectiveStatus()) }}</dd>
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
        </div>
    </div>
</x-app-layout>
