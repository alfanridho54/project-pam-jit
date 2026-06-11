<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="text-slate-300 font-semibold" />
            <x-text-input id="email" class="block mt-1 w-full bg-slate-950/50 border-slate-800 text-white placeholder-slate-600 focus:border-indigo-500 focus:ring-indigo-500/20" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" class="text-slate-300 font-semibold" />

            <x-text-input id="password" class="block mt-1 w-full bg-slate-950/50 border-slate-800 text-white placeholder-slate-600 focus:border-indigo-500 focus:ring-indigo-500/20"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-400" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-slate-800 bg-slate-950/50 text-indigo-600 shadow-sm focus:ring-indigo-500/20" name="remember">
                <span class="ms-2 text-sm text-slate-400 hover:text-slate-300 transition">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-6">
            @if (Route::has('password.request'))
                <a class="text-sm text-slate-400 hover:text-indigo-400 transition" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3 bg-indigo-600 hover:bg-indigo-500 text-white">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

    <div class="mt-8">
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-slate-800"></div>
            </div>
            <div class="relative flex justify-center text-xs uppercase">
                <span class="bg-slate-900 px-3 text-slate-500 font-medium tracking-wider">{{ __('Or secure sign-in') }}</span>
            </div>
        </div>

        <a href="{{ route('auth.google.redirect') }}" class="mt-6 flex w-full items-center justify-center rounded-md border border-slate-800 bg-slate-950/30 px-4 py-2.5 text-sm font-semibold text-slate-300 shadow-sm hover:bg-slate-950/60 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
            <svg class="h-4 w-4 mr-2.5" viewBox="0 0 24 24" width="24" height="24" xmlns="http://www.w3.org/2000/svg">
                <g transform="matrix(1, 0, 0, 1, 0, 0)">
                    <path d="M21.35,11.1H12v2.7h5.38C16.88,15.63,14.77,17,12,17a5,5,0,1,1,0-10,4.86,4.86,0,0,1,3.41,1.4l2.06-2.06A7.85,7.85,0,0,0,12,4a8,8,0,1,0,8,8A7.36,7.36,0,0,0,21.35,11.1Z" fill="currentColor"/>
                </g>
            </svg>
            {{ __('Continue with Google') }}
        </a>
    </div>
</x-guest-layout>
