<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">{{ __('Administration') }}</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900">
                    {{ __('Mail Test') }}
                </h2>
            </div>

            <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                {{ __('Back to Admin Dashboard') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <div class="mb-6">
                    <h3 class="text-base font-semibold text-gray-900">{{ __('Send Test Email') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ __('Use this admin-only page to verify the current Laravel mail configuration. The test message contains no PAM secrets.') }}
                    </p>
                </div>

                <dl class="mb-6 grid gap-4 rounded-md bg-gray-50 p-4 sm:grid-cols-3">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Mailer') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $mailer }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('From Address') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $fromAddress }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('From Name') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $fromName }}</dd>
                    </div>
                </dl>

                <form method="POST" action="{{ route('admin.mail-test.send') }}" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="recipient_email" :value="__('Recipient Email')" />
                        <x-text-input id="recipient_email" name="recipient_email" type="email" class="mt-1 block w-full" :value="old('recipient_email', Auth::user()->email)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('recipient_email')" />
                    </div>

                    <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        {{ __('The test email includes only the app name, current timestamp, app URL, and a test note. It never includes SSH credentials, private keys, or Proxmox token secrets.') }}
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button>
                            {{ __('Send Test Email') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
