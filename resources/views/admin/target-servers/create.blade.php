<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Infrastructure') }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                    {{ __('Add Target Server') }}
                </h2>
            </div>

            <a href="{{ route('admin.target-servers.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-800 transition">
                {{ __('Cancel') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto">
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5 bg-slate-50/20">
                    <h3 class="text-base font-bold text-slate-900">{{ __('Connection Details') }}</h3>
                    <p class="text-sm text-slate-500 mt-1">{{ __('Add standard SSH host target parameters. Secrets are encrypted using AES-256.') }}</p>
                </div>
                <form method="POST" action="{{ route('admin.target-servers.store') }}">
                    @csrf
                    <div class="p-6 sm:p-8">
                        @include('admin.target-servers.partials.form', [
                            'targetServer' => $targetServer,
                            'submitLabel' => __('Create Target Server'),
                        ])
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
