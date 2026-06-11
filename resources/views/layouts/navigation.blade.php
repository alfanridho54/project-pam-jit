<nav x-data="{ open: false }" class="bg-slate-900 border-r border-slate-800 text-slate-400 w-full md:w-64 md:min-h-screen flex flex-col shrink-0">
    <!-- Desktop Header / Mobile Bar -->
    <div class="px-6 py-5 flex items-center justify-between border-b border-slate-800 bg-slate-950 md:bg-transparent md:border-b-0">
        <div class="flex items-center space-x-3">
            <x-application-logo class="h-8 w-auto text-indigo-500 fill-current" />
            <span class="text-xl font-bold tracking-wider text-white">PAM <span class="text-indigo-400 font-medium">JIT</span></span>
        </div>
        <!-- Mobile menu button -->
        <button @click="open = ! open" class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-slate-300 hover:bg-slate-800 focus:outline-none transition">
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Navigation items (Desktop) -->
    <div class="hidden md:flex flex-col flex-1 justify-between p-4 space-y-6 overflow-y-auto">
        <div class="space-y-6">
            <!-- User Section -->
            <div>
                <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">{{ __('Main Portal') }}</p>
                <div class="space-y-1">
                    <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white border-l-4 border-indigo-500' : 'hover:bg-slate-800/50 hover:text-white' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        {{ __('Dashboard') }}
                    </a>

                    <a href="{{ route('notifications.index') }}" class="flex items-center justify-between px-3 py-2 text-sm font-medium rounded-md transition {{ request()->routeIs('notifications.*') ? 'bg-slate-800 text-white border-l-4 border-indigo-500' : 'hover:bg-slate-800/50 hover:text-white' }}">
                        <div class="flex items-center">
                            <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0M3.124 7.5A8.969 8.969 0 0 1 5.292 3m13.416 0a8.969 8.969 0 0 1 2.168 4.5" />
                            </svg>
                            {{ __('Notifications') }}
                        </div>
                        @if (Auth::user()->unreadNotifications()->count() > 0)
                            <span class="rounded-full bg-red-500/20 px-2 py-0.5 text-xs font-semibold text-red-400">
                                {{ Auth::user()->unreadNotifications()->count() }}
                            </span>
                        @endif
                    </a>
                </div>
            </div>

            @if (!Auth::user()->isAdmin())
                <!-- End-user My Access Section -->
                <div>
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">{{ __('My Access') }}</p>
                    <div class="space-y-1">
                        <a href="{{ route('requests.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition {{ request()->routeIs('requests.*') ? 'bg-slate-800 text-white border-l-4 border-indigo-500' : 'hover:bg-slate-800/50 hover:text-white' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            {{ __('My Requests') }}
                        </a>

                        <a href="{{ route('sessions.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition {{ request()->routeIs('sessions.*') ? 'bg-slate-800 text-white border-l-4 border-indigo-500' : 'hover:bg-slate-800/50 hover:text-white' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                            </svg>
                            {{ __('My Sessions') }}
                        </a>
                    </div>
                </div>
            @endif

            @if (Auth::user()->isAdmin())
                <!-- Administration Section -->
                <div>
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">{{ __('Administration') }}</p>
                    <div class="space-y-1">
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition {{ request()->routeIs('admin.dashboard') ? 'bg-slate-800 text-white border-l-4 border-indigo-500' : 'hover:bg-slate-800/50 hover:text-white' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                            </svg>
                            {{ __('Admin Dashboard') }}
                        </a>

                        <a href="{{ route('admin.target-servers.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition {{ request()->routeIs('admin.target-servers.*') ? 'bg-slate-800 text-white border-l-4 border-indigo-500' : 'hover:bg-slate-800/50 hover:text-white' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 3h13.5m-13.5-6h13.5m-13.5-3h13.5m-13.5-3h13.5" />
                            </svg>
                            {{ __('Target Servers') }}
                        </a>

                        <a href="{{ route('admin.proxmox.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition {{ request()->routeIs('admin.proxmox.*') ? 'bg-slate-800 text-white border-l-4 border-indigo-500' : 'hover:bg-slate-800/50 hover:text-white' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-.778.099-1.533.284-2.253" />
                            </svg>
                            {{ __('Proxmox') }}
                        </a>

                        <a href="{{ route('admin.access-requests.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition {{ request()->routeIs('admin.access-requests.*') ? 'bg-slate-800 text-white border-l-4 border-indigo-500' : 'hover:bg-slate-800/50 hover:text-white' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-.621-.504-1.125-1.125-1.125H9.75M3 5.25h18M3 12h18M3 18.75h18" />
                            </svg>
                            {{ __('Access Requests') }}
                        </a>

                        <a href="{{ route('admin.sessions.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition {{ request()->routeIs('admin.sessions.*') ? 'bg-slate-800 text-white border-l-4 border-indigo-500' : 'hover:bg-slate-800/50 hover:text-white' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                            {{ __('JIT Sessions') }}
                        </a>

                        <a href="{{ route('admin.audit-logs.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition {{ request()->routeIs('admin.audit-logs.*') ? 'bg-slate-800 text-white border-l-4 border-indigo-500' : 'hover:bg-slate-800/50 hover:text-white' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                            </svg>
                            {{ __('Audit Logs') }}
                        </a>

                        <a href="{{ route('admin.command-logs.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition {{ request()->routeIs('admin.command-logs.*') ? 'bg-slate-800 text-white border-l-4 border-indigo-500' : 'hover:bg-slate-800/50 hover:text-white' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" />
                            </svg>
                            {{ __('Command Logs') }}
                        </a>

                        <a href="{{ route('admin.mail-test.show') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition {{ request()->routeIs('admin.mail-test.*') ? 'bg-slate-800 text-white border-l-4 border-indigo-500' : 'hover:bg-slate-800/50 hover:text-white' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                            </svg>
                            {{ __('Mail Test') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Settings Dropdown at Sidebar bottom -->
        <div class="pt-4 border-t border-slate-800">
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button class="flex items-center w-full px-3 py-2 text-sm font-medium rounded-md hover:bg-slate-800 hover:text-white transition">
                        <div class="flex-1 text-left truncate">
                            <p class="text-white text-xs font-semibold">{{ Auth::user()->name }}</p>
                            <p class="text-slate-500 text-xxs truncate">{{ Auth::user()->email }}</p>
                        </div>
                        <svg class="ml-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <x-dropdown-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-dropdown-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>
    </div>

    <!-- Mobile Navigation Drawer -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden md:hidden bg-slate-950 border-t border-slate-800 px-4 py-4 space-y-4">
        <div class="space-y-1">
            <a href="{{ route('dashboard') }}" class="block px-3 py-2 text-sm font-semibold rounded-md text-slate-300 hover:text-white hover:bg-slate-800">{{ __('Dashboard') }}</a>
            <a href="{{ route('notifications.index') }}" class="block px-3 py-2 text-sm font-semibold rounded-md text-slate-300 hover:text-white hover:bg-slate-800">
                {{ __('Notifications') }}
                @if (Auth::user()->unreadNotifications()->count() > 0)
                    <span class="ml-1 rounded-full bg-red-500/20 px-2 py-0.5 text-xs text-red-400">
                        {{ Auth::user()->unreadNotifications()->count() }}
                    </span>
                @endif
            </a>

            @if (!Auth::user()->isAdmin())
                <a href="{{ route('requests.index') }}" class="block px-3 py-2 text-sm font-semibold rounded-md text-slate-300 hover:text-white hover:bg-slate-800">{{ __('My Requests') }}</a>
                <a href="{{ route('sessions.index') }}" class="block px-3 py-2 text-sm font-semibold rounded-md text-slate-300 hover:text-white hover:bg-slate-800">{{ __('My Sessions') }}</a>
            @endif

            @if (Auth::user()->isAdmin())
                <div class="pt-2 border-t border-slate-800">
                    <p class="px-3 text-xxs font-bold text-slate-500 uppercase mb-1">{{ __('Administration') }}</p>
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 text-sm font-semibold rounded-md text-slate-300 hover:text-white hover:bg-slate-800">{{ __('Admin Dashboard') }}</a>
                    <a href="{{ route('admin.target-servers.index') }}" class="block px-3 py-2 text-sm font-semibold rounded-md text-slate-300 hover:text-white hover:bg-slate-800">{{ __('Target Servers') }}</a>
                    <a href="{{ route('admin.proxmox.index') }}" class="block px-3 py-2 text-sm font-semibold rounded-md text-slate-300 hover:text-white hover:bg-slate-800">{{ __('Proxmox') }}</a>
                    <a href="{{ route('admin.access-requests.index') }}" class="block px-3 py-2 text-sm font-semibold rounded-md text-slate-300 hover:text-white hover:bg-slate-800">{{ __('Access Requests') }}</a>
                    <a href="{{ route('admin.sessions.index') }}" class="block px-3 py-2 text-sm font-semibold rounded-md text-slate-300 hover:text-white hover:bg-slate-800">{{ __('JIT Sessions') }}</a>
                    <a href="{{ route('admin.audit-logs.index') }}" class="block px-3 py-2 text-sm font-semibold rounded-md text-slate-300 hover:text-white hover:bg-slate-800">{{ __('Audit Logs') }}</a>
                    <a href="{{ route('admin.command-logs.index') }}" class="block px-3 py-2 text-sm font-semibold rounded-md text-slate-300 hover:text-white hover:bg-slate-800">{{ __('Command Logs') }}</a>
                    <a href="{{ route('admin.mail-test.show') }}" class="block px-3 py-2 text-sm font-semibold rounded-md text-slate-300 hover:text-white hover:bg-slate-800">{{ __('Mail Test') }}</a>
                </div>
            @endif
        </div>

        <div class="pt-4 border-t border-slate-800 flex items-center justify-between">
            <div>
                <p class="text-white text-xs font-semibold">{{ Auth::user()->name }}</p>
                <p class="text-slate-500 text-xxs truncate">{{ Auth::user()->email }}</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('profile.edit') }}" class="text-xs text-slate-400 hover:text-white bg-slate-800 px-2.5 py-1 rounded">{{ __('Profile') }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs text-red-400 hover:text-red-300 bg-red-950/30 border border-red-900/30 px-2.5 py-1 rounded">{{ __('Log Out') }}</button>
                </form>
            </div>
        </div>
    </div>
</nav>
