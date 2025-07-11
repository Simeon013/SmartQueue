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
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-[#fdf6ee] overflow-x-hidden">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="flex flex-col justify-between w-64 bg-[#fcf3e6] border-r border-[#f2e6d8] py-6 px-4">
            <div>
                <div class="flex items-center px-2 mb-8">
                    <span class="text-2xl font-extrabold text-blue-700">VirtualQ</span>
                </div>
                <nav class="flex flex-col gap-1">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg font-medium text-base {{ request()->routeIs('admin.dashboard') ? 'bg-blue-900 text-white' : 'text-gray-700 hover:bg-blue-50' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Dashboard
                    </a>
                    <a href="{{ route('admin.establishment.settings') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg font-medium text-base {{ request()->routeIs('admin.establishment.settings') ? 'bg-blue-900 text-white' : 'text-gray-700 hover:bg-blue-50' }}">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Paramètres établissement
                    </a>
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
                    <a href="#" class="flex items-center gap-3 px-4 py-2 text-base font-medium text-gray-700 rounded-lg hover:bg-blue-50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Utilisateurs
                    </a>
                </nav>
            </div>
            <div class="flex items-center gap-3 px-2 mt-8">
                @php
                    $user = Auth::user();
                    $initials = collect(explode(' ', $user->name))->map(fn($w) => strtoupper($w[0]))->join('');
                @endphp
                <div class="flex items-center justify-center w-10 h-10 text-lg font-bold text-white bg-blue-700 rounded-full">{{ $initials }}</div>
                <div>
                    <div class="text-sm font-bold text-gray-900">{{ $user->name }}</div>
                    <div class="text-xs text-gray-500">Admin</div>
                </div>
            </div>
        </aside>
        <!-- Main Content -->
        <div class="flex flex-col flex-1 min-h-screen">
            <!-- Top Navigation -->
            <header class="flex items-center justify-between px-10 py-4 bg-white shadow-sm">
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
                            <div class="flex items-center justify-center w-8 h-8 text-base font-bold text-white bg-blue-700 rounded-full">{{ $initials }}</div>
                            <span class="font-semibold text-gray-700">{{ $user->name }}</span>
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
            <!-- Page Content -->
            <main class="flex-1 p-10">
                @if(session('success'))
                    <div class="p-4 mb-4 text-green-700 bg-green-100 border border-green-400 rounded">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="p-4 mb-4 text-red-700 bg-red-100 border border-red-400 rounded">
                        {{ session('error') }}
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    @livewireScripts
</body>
</html>
