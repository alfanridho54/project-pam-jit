<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Access Request') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                @if ($targetServers->isEmpty())
                    <div class="rounded-md bg-yellow-50 p-4 text-sm text-yellow-800">
                        {{ __('There are no active target servers available for requests.') }}
                    </div>
                @else
                    <form method="POST" action="{{ route('requests.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="target_server_id" :value="__('Target Server')" />
                            <select id="target_server_id" name="target_server_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">{{ __('Select a target server') }}</option>
                                @foreach ($targetServers as $targetServer)
                                    <option value="{{ $targetServer->id }}" @selected((string) old('target_server_id') === (string) $targetServer->id)>
                                        {{ $targetServer->name }} ({{ $targetServer->host }}:{{ $targetServer->port }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('target_server_id')" />
                        </div>

                        <div>
                            <x-input-label for="requested_duration_minutes" :value="__('Requested Duration')" />
                            <x-text-input id="requested_duration_minutes" name="requested_duration_minutes" type="number" min="5" max="120" class="mt-1 block w-full" :value="old('requested_duration_minutes', 30)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('requested_duration_minutes')" />
                        </div>

                        <div>
                            <x-input-label for="reason" :value="__('Reason')" />
                            <textarea id="reason" name="reason" rows="7" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('reason') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('reason')" />
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('requests.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                                {{ __('Cancel') }}
                            </a>

                            <x-primary-button>
                                {{ __('Submit Request') }}
                            </x-primary-button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
