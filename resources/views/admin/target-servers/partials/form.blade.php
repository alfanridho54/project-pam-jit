<div class="space-y-6 text-slate-800">
    <div>
        <x-input-label for="name" :value="__('Name')" class="font-bold text-slate-700" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full border-slate-200 focus:border-indigo-500 focus:ring-indigo-500/20" :value="old('name', $targetServer->name)" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div class="grid gap-6 sm:grid-cols-3">
        <div class="sm:col-span-2">
            <x-input-label for="host" :value="__('Host IP / DNS')" class="font-bold text-slate-700" />
            <x-text-input id="host" name="host" type="text" class="mt-1 block w-full border-slate-200 font-mono text-sm" :value="old('host', $targetServer->host)" required />
            <x-input-error class="mt-2" :messages="$errors->get('host')" />
        </div>

        <div>
            <x-input-label for="port" :value="__('Port')" class="font-bold text-slate-700" />
            <x-text-input id="port" name="port" type="number" min="1" max="65535" class="mt-1 block w-full border-slate-200 font-mono text-sm" :value="old('port', $targetServer->port ?? 22)" required />
            <x-input-error class="mt-2" :messages="$errors->get('port')" />
        </div>
    </div>

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <x-input-label for="ssh_username" :value="__('SSH Username')" class="font-bold text-slate-700" />
            <x-text-input id="ssh_username" name="ssh_username" type="text" class="mt-1 block w-full border-slate-200" :value="old('ssh_username', $targetServer->ssh_username)" />
            <x-input-error class="mt-2" :messages="$errors->get('ssh_username')" />
        </div>

        <div>
            <x-input-label for="auth_type" :value="__('Authentication Type')" class="font-bold text-slate-700" />
            <select id="auth_type" name="auth_type" class="mt-1 block w-full rounded-lg border-slate-200 bg-white text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/20 py-2.5">
                <option value="password" @selected(old('auth_type', $targetServer->auth_type) === 'password')>{{ __('Password') }}</option>
                <option value="private_key" @selected(old('auth_type', $targetServer->auth_type) === 'private_key')>{{ __('Private key') }}</option>
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('auth_type')" />
        </div>
    </div>

    @if ($targetServer->exists)
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
            <span class="block text-xxs font-bold uppercase tracking-wider text-slate-400 mb-2">{{ __('Stored Credential Telemetry') }}</span>
            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center rounded-full bg-white px-2.5 py-0.5 text-xs font-semibold text-slate-600 border border-slate-200">
                    {{ __('Password') }}: <span class="ml-1 {{ $targetServer->hasPassword() ? 'text-emerald-700' : 'text-slate-400' }}">{{ $targetServer->hasPassword() ? __('Configured') : __('Missing') }}</span>
                </span>
                <span class="inline-flex items-center rounded-full bg-white px-2.5 py-0.5 text-xs font-semibold text-slate-600 border border-slate-200">
                    {{ __('Private Key') }}: <span class="ml-1 {{ $targetServer->hasPrivateKey() ? 'text-emerald-700' : 'text-slate-400' }}">{{ $targetServer->hasPrivateKey() ? __('Configured') : __('Missing') }}</span>
                </span>
            </div>
            <p class="mt-2.5 text-xs text-slate-400">{{ __('Leave secret input fields blank to preserve the encrypted values stored in the database.') }}</p>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <div>
            <x-input-label for="ssh_password" :value="__('SSH Password')" class="font-bold text-slate-700" />
            <x-text-input id="ssh_password" name="ssh_password" type="password" class="mt-1 block w-full border-slate-200 focus:border-indigo-500 focus:ring-indigo-500/20" autocomplete="new-password" placeholder="••••••••" />
            <x-input-error class="mt-2" :messages="$errors->get('ssh_password')" />
        </div>

        <div>
            <x-input-label for="ssh_private_key" :value="__('SSH Private Key')" class="font-bold text-slate-700" />
            <textarea id="ssh_private_key" name="ssh_private_key" rows="4" class="mt-1 block w-full rounded-lg border-slate-200 font-mono text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500/20" placeholder="-----BEGIN OPENSSH PRIVATE KEY-----"></textarea>
            <x-input-error class="mt-2" :messages="$errors->get('ssh_private_key')" />
        </div>
    </div>

    <div>
        <x-input-label for="description" :value="__('Description')" class="font-bold text-slate-700" />
        <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/20" placeholder="Infrastructure notes or purpose of this node...">{{ old('description', $targetServer->description) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>

    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
        <input type="hidden" name="is_active" value="0">
        <label for="is_active" class="inline-flex items-center cursor-pointer">
            <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500/20" @checked(old('is_active', $targetServer->is_active ?? true))>
            <span class="ms-2 text-sm font-bold text-slate-700">{{ __('Active Status') }}</span>
        </label>
        <p class="mt-1 text-xs text-slate-500">{{ __('Only active servers can be selected by end-users during access request flows.') }}</p>
        <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
    </div>

    <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-6">
        <a href="{{ route('admin.target-servers.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition">
            {{ __('Cancel') }}
        </a>

        <x-primary-button class="bg-indigo-600 hover:bg-indigo-500 px-5 py-2.5 text-sm">
            {{ $submitLabel }}
        </x-primary-button>
    </div>
</div>
