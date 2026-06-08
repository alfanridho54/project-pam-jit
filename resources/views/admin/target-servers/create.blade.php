<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-gray-500">{{ __('Target Servers') }}</p>
            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900">
                {{ __('Add Target Server') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">{{ __('Connection Details') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Credentials are encrypted when saved and never shown again.') }}</p>
                </div>
                <form method="POST" action="{{ route('admin.target-servers.store') }}" class="space-y-6">
                    @csrf
                    <div class="p-6">
                        @include('admin.target-servers.partials.form', [
                            'targetServer' => $targetServer,
                            'submitLabel' => __('Create Target Server'),
                        ])
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
