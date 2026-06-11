<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('My Access') }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                    {{ __('New Access Request') }}
                </h2>
            </div>
            <a href="{{ route('requests.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-800 transition">
                {{ __('Cancel') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-6 sm:p-8">
                @if ($targetServers->isEmpty())
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-800 shadow-sm flex items-start">
                        <svg class="h-5 w-5 text-amber-600 mr-3 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        <div>{{ __('There are no active target servers available for requests.') }}</div>
                    </div>
                @else
                    <form method="POST" action="{{ route('requests.store') }}" class="space-y-6">
                        @csrf

                        <!-- Search Target Server -->
                        <div class="bg-slate-50/50 p-4 rounded-xl border border-slate-100">
                            <x-input-label for="target_server_search" :value="__('Find Target Server')" class="font-bold text-slate-700" />
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
                                    </svg>
                                </div>
                                <x-text-input id="target_server_search" type="search" class="pl-9 block w-full bg-white border-slate-200 focus:border-indigo-500 focus:ring-indigo-500/20" placeholder="Search server by name, hostname, IP, username, VMID, or node..." />
                            </div>
                        </div>

                        <!-- Dropdown Select -->
                        <div>
                            <x-input-label for="target_server_id" :value="__('Target Server')" class="font-bold text-slate-700" />
                            <select id="target_server_id" name="target_server_id" class="mt-1 block w-full rounded-lg border-slate-200 bg-white text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/20 py-2.5" required>
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
                            <p class="mt-2 text-xs text-slate-500">{{ __('Choose the server you want temporary access to.') }}</p>
                            <p id="target_server_search_empty" class="mt-2 hidden text-xs font-semibold text-amber-700 bg-amber-50 p-2.5 rounded-lg border border-amber-200">{{ __('No active target servers match this search.') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('target_server_id')" />
                        </div>

                        <!-- Requested Duration -->
                        <div>
                            <x-input-label for="duration_value" :value="__('Requested Duration')" class="font-bold text-slate-700" />
                            <div class="mt-1 grid gap-3 sm:grid-cols-3">
                                <x-text-input id="duration_value" name="duration_value" type="number" min="1" class="block w-full sm:col-span-2 border-slate-200" :value="old('duration_value', 30)" required />
                                <select id="duration_unit" name="duration_unit" class="block w-full rounded-lg border-slate-200 bg-white text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/20 py-2.5" required>
                                    <option value="minutes" @selected(old('duration_unit', 'minutes') === 'minutes')>{{ __('Minutes') }}</option>
                                    <option value="hours" @selected(old('duration_unit') === 'hours')>{{ __('Hours') }}</option>
                                    <option value="days" @selected(old('duration_unit') === 'days')>{{ __('Days') }}</option>
                                </select>
                            </div>
                            <p class="mt-2 text-xs text-slate-500">{{ __('Allowed ranges: 5-120 minutes, 1-24 hours, or 1-7 days.') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('duration_value')" />
                            <x-input-error class="mt-2" :messages="$errors->get('duration_unit')" />
                        </div>

                        <!-- Reason -->
                        <div>
                            <x-input-label for="reason" :value="__('Access Request Reason')" class="font-bold text-slate-700" />
                            <textarea id="reason" name="reason" rows="6" class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/20" placeholder="Provide a detailed business justification for this temporary session..." required>{{ old('reason') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('reason')" />
                        </div>

                        <!-- Action buttons -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                            <a href="{{ route('requests.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition">
                                {{ __('Cancel') }}
                            </a>

                            <x-primary-button class="bg-indigo-600 hover:bg-indigo-500 px-5 py-2.5 text-sm">
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
