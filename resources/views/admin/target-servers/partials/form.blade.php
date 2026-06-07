<div>
    <x-input-label for="name" :value="__('Name')" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $targetServer->name)" required autofocus />
    <x-input-error class="mt-2" :messages="$errors->get('name')" />
</div>

<div class="grid gap-6 sm:grid-cols-3">
    <div class="sm:col-span-2">
        <x-input-label for="host" :value="__('Host')" />
        <x-text-input id="host" name="host" type="text" class="mt-1 block w-full" :value="old('host', $targetServer->host)" required />
        <x-input-error class="mt-2" :messages="$errors->get('host')" />
    </div>

    <div>
        <x-input-label for="port" :value="__('Port')" />
        <x-text-input id="port" name="port" type="number" min="1" max="65535" class="mt-1 block w-full" :value="old('port', $targetServer->port ?? 22)" required />
        <x-input-error class="mt-2" :messages="$errors->get('port')" />
    </div>
</div>

<div>
    <x-input-label for="ssh_username" :value="__('SSH Username')" />
    <x-text-input id="ssh_username" name="ssh_username" type="text" class="mt-1 block w-full" :value="old('ssh_username', $targetServer->ssh_username)" />
    <x-input-error class="mt-2" :messages="$errors->get('ssh_username')" />
</div>

<div>
    <x-input-label for="auth_type" :value="__('Authentication Type')" />
    <select id="auth_type" name="auth_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="password" @selected(old('auth_type', $targetServer->auth_type) === 'password')>{{ __('Password') }}</option>
        <option value="private_key" @selected(old('auth_type', $targetServer->auth_type) === 'private_key')>{{ __('Private key') }}</option>
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('auth_type')" />
</div>

@if ($targetServer->exists)
    <div class="rounded-md bg-gray-50 p-4 text-sm text-gray-700">
        <div>{{ __('Existing password') }}: {{ $targetServer->hasPassword() ? __('Set') : __('Missing') }}</div>
        <div>{{ __('Existing private key') }}: {{ $targetServer->hasPrivateKey() ? __('Set') : __('Missing') }}</div>
        <p class="mt-2 text-gray-500">{{ __('Leave secret fields blank to keep the stored encrypted values.') }}</p>
    </div>
@endif

<div>
    <x-input-label for="ssh_password" :value="__('SSH Password')" />
    <x-text-input id="ssh_password" name="ssh_password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
    <x-input-error class="mt-2" :messages="$errors->get('ssh_password')" />
</div>

<div>
    <x-input-label for="ssh_private_key" :value="__('SSH Private Key')" />
    <textarea id="ssh_private_key" name="ssh_private_key" rows="8" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
    <x-input-error class="mt-2" :messages="$errors->get('ssh_private_key')" />
</div>

<div>
    <x-input-label for="description" :value="__('Description')" />
    <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $targetServer->description) }}</textarea>
    <x-input-error class="mt-2" :messages="$errors->get('description')" />
</div>

<div>
    <input type="hidden" name="is_active" value="0">
    <label for="is_active" class="inline-flex items-center">
        <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('is_active', $targetServer->is_active ?? true))>
        <span class="ms-2 text-sm text-gray-600">{{ __('Active') }}</span>
    </label>
    <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
</div>

<div class="flex items-center justify-end gap-4">
    <a href="{{ route('admin.target-servers.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
        {{ __('Cancel') }}
    </a>

    <x-primary-button>
        {{ $submitLabel }}
    </x-primary-button>
</div>
