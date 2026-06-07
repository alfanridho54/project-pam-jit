<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Target Server') }}
            </h2>

            <a href="{{ route('admin.target-servers.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                {{ __('Back to Target Servers') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('admin.target-servers.update', $targetServer) }}" class="space-y-6">
                    @csrf
                    @method('PATCH')

                    @include('admin.target-servers.partials.form', [
                        'targetServer' => $targetServer,
                        'submitLabel' => __('Update Target Server'),
                    ])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
