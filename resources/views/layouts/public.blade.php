<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SmartQueue') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Custom Public CSS -->
    <link href="{{ asset('css/public.css') }}" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Styles -->
    @livewireStyles
</head>
<body class="public-container">
    <!-- Header -->
    <header class="public-header w-full py-4 px-6 shadow-sm">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="{{ route('public.queues.index') }}" class="flex items-center">
                <span class="text-2xl font-bold text-blue-600">SmartQueue</span>
            </a>
            <nav class="hidden md:flex space-x-8">
                <a href="{{ route('public.queues.index') }}" class="text-gray-600 hover:text-blue-600 transition-colors">
                    Accueil
                </a>
                @auth
                    <a href="{{ route('admin') }}" class="text-gray-600 hover:text-blue-600 transition-colors">
                        Tableau de bord
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-gray-600 hover:text-blue-600 transition-colors">
                        Connexion
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    <!-- Page Content -->
    <main class="flex-grow w-full py-8 px-4">
        <div class="max-w-4xl mx-auto w-full">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white py-6 mt-12 border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-4 text-center text-gray-500 text-sm">
            &copy; {{ date('Y') }} SmartQueue. Tous droits réservés.
        </div>
    </footer>

    @livewireScripts
    
    <!-- Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>
</body>
</html>
