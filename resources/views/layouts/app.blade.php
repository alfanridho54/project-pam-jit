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
    <body class="font-sans antialiased text-slate-800 bg-slate-50/50">
        <div class="min-h-screen flex flex-col md:flex-row">
            @include('layouts.navigation')

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
                <!-- Page Heading -->
                @isset($header)
                    <header class="bg-white border-b border-slate-200 py-4 px-6 sm:px-8 flex items-center justify-between sticky top-0 z-10">
                        <div class="flex-1">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto p-6 sm:p-8">
                    @if (session('error'))
                        <div class="max-w-7xl mx-auto mb-6">
                            <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800 shadow-sm flex items-start">
                                <svg class="h-5 w-5 text-red-500 mr-3 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                </svg>
                                <div>{{ session('error') }}</div>
                            </div>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="max-w-7xl mx-auto mb-6">
                            <div class="rounded-lg border border-emerald-200 bg-emerald-50/50 p-4 text-sm font-semibold text-emerald-800 shadow-sm flex items-start">
                                <svg class="h-5 w-5 text-emerald-500 mr-3 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                <div>{{ session('success') }}</div>
                            </div>
                        </div>
                    @endif

                    <div class="max-w-7xl mx-auto">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
