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
                            <x-input-label for="target_server_search" :value="__('Find Target Server')" />
                            <x-text-input id="target_server_search" type="search" class="mt-1 block w-full" placeholder="Search by name, hostname, IP, username, VMID, or node" />
                        </div>

                        <div>
                            <x-input-label for="target_server_id" :value="__('Target Server')" />
                            <select id="target_server_id" name="target_server_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">{{ __('Select a target server') }}</option>
                                @foreach ($targetServers as $targetServer)
                                    @php
                                        $optionParts = [
                                            $targetServer->name,
                                            $targetServer->host.':'.$targetServer->port,
                                            $targetServer->ssh_username ? __('SSH: :username', ['username' => $targetServer->ssh_username]) : null,
                                            $targetServer->getAttribute('proxmox_vmid') ? __('VMID: :vmid', ['vmid' => $targetServer->getAttribute('proxmox_vmid')]) : null,
                                            $targetServer->getAttribute('proxmox_node') ? __('Node: :node', ['node' => $targetServer->getAttribute('proxmox_node')]) : null,
                                        ];
                                        $optionLabel = implode(' | ', array_filter($optionParts));
                                    @endphp
                                    <option value="{{ $targetServer->id }}" data-search="{{ strtolower($optionLabel) }}" @selected((string) old('target_server_id') === (string) $targetServer->id)>
                                        {{ $optionLabel }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-2 text-sm text-gray-500">{{ __('Choose the server you want temporary access to.') }}</p>
                            <p id="target_server_search_empty" class="mt-2 hidden text-sm text-amber-700">{{ __('No active target servers match this search.') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('target_server_id')" />
                        </div>

                        <div>
                            <x-input-label for="duration_value" :value="__('Requested Duration')" />
                            <div class="mt-1 grid gap-3 sm:grid-cols-3">
                                <x-text-input id="duration_value" name="duration_value" type="number" min="1" class="block w-full sm:col-span-2" :value="old('duration_value', 30)" required />
                                <select id="duration_unit" name="duration_unit" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="minutes" @selected(old('duration_unit', 'minutes') === 'minutes')>{{ __('Minutes') }}</option>
                                    <option value="hours" @selected(old('duration_unit') === 'hours')>{{ __('Hours') }}</option>
                                    <option value="days" @selected(old('duration_unit') === 'days')>{{ __('Days') }}</option>
                                </select>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">{{ __('Allowed ranges: 5-120 minutes, 1-24 hours, or 1-7 days.') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('duration_value')" />
                            <x-input-error class="mt-2" :messages="$errors->get('duration_unit')" />
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

                    <script>
                        const targetServerSearch = document.getElementById('target_server_search');
                        const targetServerSelect = document.getElementById('target_server_id');
                        const targetServerEmpty = document.getElementById('target_server_search_empty');

                        targetServerSearch?.addEventListener('input', () => {
                            const query = targetServerSearch.value.trim().toLowerCase();
                            let visibleCount = 0;

                            Array.from(targetServerSelect.options).forEach((option) => {
                                if (option.value === '') {
                                    option.hidden = false;
                                    return;
                                }

                                const matches = option.dataset.search?.includes(query) ?? false;
                                option.hidden = ! matches;

                                if (matches) {
                                    visibleCount += 1;
                                }
                            });

                            if (targetServerSelect.selectedOptions[0]?.hidden) {
                                targetServerSelect.value = '';
                            }

                            targetServerEmpty?.classList.toggle('hidden', visibleCount > 0);
                        });
                    </script>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
