<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'VirtualQ') }} - Administration</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- jQuery seulement pour l'instant -->
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="font-sans antialiased bg-[#fdf6ee] overflow-x-hidden">
    <div class="flex min-h-screen">
        <!-- Sidebar fixe -->
        <aside class="fixed left-0 top-0 z-40 h-screen w-64 bg-[#fcf3e6] border-r border-[#f2e6d8] py-6 px-4 overflow-y-auto">
            <div class="flex flex-col h-full">
                <div class="flex items-center px-2 mb-8">
                    <span class="text-2xl font-extrabold text-blue-700">VirtualQ</span>
                </div>
                <nav class="flex flex-col gap-1 flex-1">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg font-medium text-base {{ request()->routeIs('admin.dashboard') ? 'bg-blue-900 text-white' : 'text-gray-700 hover:bg-blue-50' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Dashboard
                    </a>
                    {{-- <a href="{{ route('admin.establishment.settings') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg font-medium text-base {{ request()->routeIs('admin.establishment.settings') ? 'bg-blue-900 text-white' : 'text-gray-700 hover:bg-blue-50' }}">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Paramètres établissement
                    </a> --}}
                    <a href="{{ route('admin.queues.index') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg font-medium text-base {{ request()->routeIs('admin.queues.*') ? 'bg-blue-900 text-white' : 'text-gray-700 hover:bg-blue-50' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Files d'attente
                    </a>
                    {{-- <a href="#" class="flex items-center gap-3 px-4 py-2 text-base font-medium text-gray-700 rounded-lg hover:bg-blue-50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 01-8 0"/></svg>
                        Guichets
                    </a> --}}
                    <a href="#" class="flex items-center gap-3 px-4 py-2 text-base font-medium text-gray-700 rounded-lg hover:bg-blue-50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 018 0v2m-4-4a4 4 0 100-8 4 4 0 000 8z"/></svg>
                        Statistiques
                    </a>
                    @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg font-medium text-base {{ request()->routeIs('admin.users.*') ? 'bg-blue-900 text-white' : 'text-gray-700 hover:bg-blue-50' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Utilisateurs
                    </a>
                    @endif
                    @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('admin.roles.index') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg font-medium text-base {{ request()->routeIs('admin.roles.*') ? 'bg-blue-900 text-white' : 'text-gray-700 hover:bg-blue-50' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Rôles
                    </a>
                    @endif
                </nav>
                <!-- Paramètres en bas -->
                @if(auth()->user()->isSuperAdmin())
                <div class="mt-auto mb-0">
                    <a href="{{ route('admin.settings') }}" class="flex items-center gap-3 px-4 py-2 text-base font-medium text-gray-700 rounded-lg hover:bg-blue-50 {{ request()->routeIs('admin.settings*') ? 'bg-blue-900 text-white' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Paramètres
                    </a>
                </div>
                @endif
                
                <!-- Profil utilisateur -->
                <div class="flex items-center gap-3 px-2 mt-8">
                    @php
                        $user = Auth::user();
                        $initials = collect(explode(' ', $user->name))->map(fn($w) => strtoupper($w[0]))->join('');
                        $roleName = $user->isSuperAdmin() ? 'Super Admin' : ($user->isAdmin() ? 'Administrateur' : 'Agent');
                        $roleColor = $user->isSuperAdmin() ? 'bg-purple-600' : ($user->isAdmin() ? 'bg-blue-600' : 'bg-green-600');
                    @endphp
                    <div class="flex items-center justify-center w-10 h-10 text-lg font-bold text-white {{ $roleColor }} rounded-full">{{ $initials }}</div>
                    <div>
                        <div class="text-sm font-bold text-gray-900">{{ $user->name }}</div>
                        <div class="flex items-center gap-1">
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $roleColor }} text-white font-medium">
                                {{ $roleName }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
        <!-- Main Content avec marge pour la sidebar -->
        <div class="flex flex-col flex-1 min-h-screen ml-64">
            <!-- Header fixe -->
            <header class="fixed top-0 right-0 left-64 z-30 flex items-center justify-between px-10 py-4 bg-white shadow-sm">
                <div class="flex items-center gap-4">
                    <h2 class="text-2xl font-extrabold tracking-tight text-gray-900">@yield('header')</h2>
                </div>
                <div class="flex items-center gap-6">
                    {{-- <button class="relative">
                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span class="absolute -top-1 -right-1 bg-blue-600 text-white text-xs font-bold px-1.5 py-0.5 rounded-full">5</span>
                    </button> --}}
                    <!-- Dropdown menu profil -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @keydown.escape="open = false" type="button" class="flex items-center gap-2 focus:outline-none" :aria-expanded="open" aria-haspopup="true">
                            <div class="flex items-center justify-center w-8 h-8 text-base font-bold text-white {{ $roleColor }} rounded-full">{{ $initials }}</div>
                            <div class="flex flex-col items-end">
                                <span class="font-semibold text-gray-700">{{ $user->name }}</span>
                                <span class="text-xs px-1.5 py-0.5 rounded-full {{ $roleColor }} text-white font-medium">
                                    {{ $roleName }}
                                </span>
                            </div>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 left-auto z-50 w-56 max-w-xs mt-2 bg-white border border-gray-200 rounded shadow-lg" style="display: none; overflow-x: hidden;">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-gray-700 hover:bg-blue-50">Profil</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 text-left text-gray-700 hover:bg-blue-50">Déconnexion</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>
            <!-- Page Content avec marge pour le header -->
            <main class="flex-1 overflow-y-auto bg-gray-50 pt-16">
                <!-- Notifications -->
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 mx-4 mt-4" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                            <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <title>Close</title>
                                <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                            </svg>
                        </span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 mx-4 mt-4" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                            <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <title>Close</title>
                                <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                            </svg>
                        </span>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4 mx-4 mt-4" role="alert">
                        <span class="block sm:inline">{{ session('warning') }}</span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                            <svg class="fill-current h-6 w-6 text-yellow-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <title>Close</title>
                                <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                            </svg>
                        </span>
                    </div>
                @endif

                @if(session('info'))
                    <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4 mx-4 mt-4" role="alert">
                        <span class="block sm:inline">{{ session('info') }}</span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                            <svg class="fill-current h-6 w-6 text-blue-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <title>Close</title>
                                <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                            </svg>
                        </span>
                    </div>
                @endif

                <!-- Page Content -->
                <div class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        @yield('content')
                    </div>
                </div>
            </main>
        </div>
    </div>
    @livewireScripts
    @stack('scripts')
</body>
</html>
