<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Administration') }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                    {{ __('Mail System Verification') }}
                </h2>
            </div>

            <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-900 transition">
                {{ __('Back to Admin Dashboard') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-6 sm:p-8 space-y-6">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">{{ __('Send Diagnostic Email') }}</h3>
                    <p class="text-sm text-slate-500 mt-1">
                        {{ __('Use this page to verify outbound mail driver settings. Diagnostic messages do not contain any session credentials or secrets.') }}
                    </p>
                </div>

                <!-- Config metadata details list -->
                <dl class="grid gap-4 rounded-xl border border-slate-150 bg-slate-50/50 p-4 sm:grid-cols-3 text-sm">
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Active Mailer') }}</dt>
                        <dd class="mt-1 font-semibold text-slate-900 font-mono">{{ $mailer }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Sender Address') }}</dt>
                        <dd class="mt-1 font-semibold text-slate-900 truncate font-mono text-xs">{{ $fromAddress }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Sender Name') }}</dt>
                        <dd class="mt-1 font-semibold text-slate-900 truncate">{{ $fromName }}</dd>
                    </div>
                </dl>

                <form method="POST" action="{{ route('admin.mail-test.send') }}" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="recipient_email" :value="__('Recipient Email Address')" class="font-bold text-slate-700" />
                        <x-text-input id="recipient_email" name="recipient_email" type="email" class="mt-1 block w-full border-slate-200" :value="old('recipient_email', Auth::user()->email)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('recipient_email')" />
                    </div>

                    <!-- Security Alert -->
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-800 flex items-start">
                        <svg class="h-4.5 w-4.5 text-amber-600 mr-2.5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                        </svg>
                        <span>{{ __('The verification email contains only standard system diagnostics, a timestamp, and base URLs. It never includes active target SSH passwords, keys, or token secrets.') }}</span>
                    </div>

                    <div class="flex justify-end pt-2 border-t border-slate-100">
                        <x-primary-button class="bg-indigo-600 hover:bg-indigo-500 px-5 py-2.5 text-sm">
                            {{ __('Send Diagnostic Email') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
