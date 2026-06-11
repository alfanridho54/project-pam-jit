<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&family=Fira+Code:wght@300..700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-800 antialiased bg-slate-950">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-slate-900 via-slate-950 to-black relative overflow-hidden">
            <div class="absolute inset-0 bg-[linear-gradient(to_right,#0f172a_1px,transparent_1px),linear-gradient(to_bottom,#0f172a_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)] opacity-30"></div>
            
            <div class="relative z-10 flex flex-col items-center">
                <a href="/">
                    <x-application-logo class="w-16 h-16 text-indigo-500 fill-current" />
                </a>
                <h1 class="mt-4 text-2xl font-bold tracking-tight text-white">
                    PAM <span class="text-indigo-400 font-medium">JIT Portal</span>
                </h1>
                <p class="mt-1 text-sm text-slate-500">{{ __('Privileged Access Management') }}</p>
            </div>

            <div class="w-full sm:max-w-md mt-8 px-8 py-6 bg-slate-900/60 backdrop-blur-xl border border-slate-800 shadow-2xl overflow-hidden sm:rounded-xl relative z-10 text-slate-300">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
