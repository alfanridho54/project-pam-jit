<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Target Server') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('admin.target-servers.store') }}" class="space-y-6">
                    @csrf

                    @include('admin.target-servers.partials.form', [
                        'targetServer' => $targetServer,
                        'submitLabel' => __('Create Target Server'),
                    ])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
