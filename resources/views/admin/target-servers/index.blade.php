<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">{{ __('Infrastructure') }}</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900">
                    {{ __('Target Servers') }}
                </h2>
            </div>

            <a href="{{ route('admin.target-servers.create') }}" class="inline-flex items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">
                {{ __('Add Server') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('ssh_test_result'))
                <div class="mb-6 rounded-lg border {{ session('ssh_test_result.ok') ? 'border-emerald-200 bg-emerald-50' : 'border-red-200 bg-red-50' }} p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-sm font-semibold {{ session('ssh_test_result.ok') ? 'text-emerald-900' : 'text-red-900' }}">{{ __('SSH Test Result') }}</h3>
                            <p class="mt-1 text-sm {{ session('ssh_test_result.ok') ? 'text-emerald-700' : 'text-red-700' }}">
                                {{ session('ssh_test_result.message') }}
                            </p>
                        </div>
                        <x-badge :status="session('ssh_test_result.ok') ? 'success' : 'failed'" />
                    </div>

                    @if (! empty(session('ssh_test_result.details.output')))
                        <pre class="mt-4 overflow-x-auto rounded-md bg-gray-950 p-4 text-sm text-gray-100">{{ session('ssh_test_result.details.output') }}</pre>
                    @endif
                </div>
            @endif

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-5 py-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">{{ __('Managed SSH Targets') }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ __('Servers users can request temporary JIT access to.') }}</p>
                        </div>

                        <form method="GET" action="{{ route('admin.target-servers.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                            <div>
                                <x-input-label for="q" :value="__('Search')" />
                                <x-text-input id="q" name="q" type="search" class="mt-1 block w-full sm:w-80" :value="$q" placeholder="Search by name, hostname, IP, username, VMID, or node" />
                            </div>

                            <div>
                                <x-input-label for="status" :value="__('Status')" />
                                <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-36">
                                    <option value="">{{ __('Any status') }}</option>
                                    <option value="active" @selected($status === 'active')>{{ __('Active') }}</option>
                                    <option value="inactive" @selected($status === 'inactive')>{{ __('Inactive') }}</option>
                                </select>
                            </div>

                            <div class="flex items-center gap-3">
                                <button type="submit" class="inline-flex items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">
                                    {{ __('Search') }}
                                </button>

                                @if ($q !== '' || filled($status))
                                    <a href="{{ route('admin.target-servers.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                                        {{ __('Reset') }}
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Name') }}</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Host') }}</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Auth') }}</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Credentials') }}</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Status') }}</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($targetServers as $targetServer)
                                <tr class="hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-gray-900">{{ $targetServer->name }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 font-mono text-sm text-gray-600">
                                        {{ $targetServer->host }}:{{ $targetServer->port }}
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">
                                        <x-badge :status="str_replace('_', ' ', $targetServer->auth_type)" />
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-600">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="inline-flex rounded-full bg-gray-50 px-2.5 py-0.5 text-xs font-medium text-gray-700 ring-1 ring-gray-300">
                                                {{ __('Password') }}: {{ $targetServer->hasPassword() ? __('Set') : __('Missing') }}
                                            </span>
                                            <span class="inline-flex rounded-full bg-gray-50 px-2.5 py-0.5 text-xs font-medium text-gray-700 ring-1 ring-gray-300">
                                                {{ __('Private key') }}: {{ $targetServer->hasPrivateKey() ? __('Set') : __('Missing') }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm">
                                        <x-badge :status="$targetServer->is_active ? 'active' : 'inactive'" />
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right text-sm">
                                        <div class="flex justify-end gap-3">
                                            <a href="{{ route('admin.target-servers.edit', $targetServer) }}" class="font-medium text-indigo-600 hover:text-indigo-900">
                                                {{ __('Edit') }}
                                            </a>

                                            <form method="POST" action="{{ route('admin.target-servers.test-connection', $targetServer) }}">
                                                @csrf

                                                <button type="submit" class="font-medium text-gray-700 hover:text-gray-950">
                                                    {{ __('Test') }}
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.target-servers.destroy', $targetServer) }}" onsubmit="return confirm('{{ __('Delete this target server?') }}');">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="font-medium text-red-600 hover:text-red-900">
                                                    {{ __('Delete') }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-12 text-center">
                                        @if ($q !== '' || filled($status))
                                            <div class="text-sm font-medium text-gray-900">{{ __('No target servers match this search.') }}</div>
                                            <div class="mt-1 text-sm text-gray-500">{{ __('Try a different search term or clear the filters.') }}</div>
                                        @else
                                            <div class="text-sm font-medium text-gray-900">{{ __('No target servers yet') }}</div>
                                            <div class="mt-1 text-sm text-gray-500">{{ __('Create one manually or import a VM from Proxmox.') }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($targetServers->hasPages())
                    <div class="border-t border-gray-200 px-5 py-4">
                        {{ $targetServers->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
