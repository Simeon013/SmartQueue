<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .gradient-button {
            background: linear-gradient(to right, #28a745, #218838);
            border: none;
            color: white;
            padding: 15px 32px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 18px;
            font-weight: bold;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: background 0.3s ease;
        }

        .gradient-button:hover {
            background: linear-gradient(to right, #218838, #1e7e34);
        }

        .info-card {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            background-color: white;
            padding: 1.5rem; /* px-6 */
        }

        .status-badge-active {
            background-color: #34D399; /* A slightly darker green for background */
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px; /* full rounded */
            font-size: 0.875rem; /* text-sm */
            font-weight: 600; /* font-semibold */
            line-height: 1.25rem;
        }

        .status-badge-closed {
            background-color: #EF4444; /* Red color for closed */
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
            line-height: 1.25rem;
        }

        .text-custom-blue {
            color: #3B82F6; /* A more specific blue for establishment name */
        }

    </style>
</head>
<body class="flex flex-col items-center justify-center min-h-screen font-sans antialiased bg-gray-100">
    <div class="w-full px-6 py-4 mt-6 overflow-hidden bg-white shadow-md py-202 sm:max-w-md sm:rounded-lg">
        <h1 class="mb-4 text-2xl font-bold text-center text-gray-800">Confirmez votre inscription</h1>

        <div class="flex items-center justify-center mb-6 font-semibold text-center text-green-600">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            QR code validé avec succès !
        </div>

        <div class="info-card">
            <div class="py-4 border-b border-gray-200">
                <div class="flex items-center justify-between text-lg">
                    <span class="text-gray-600">Établissement</span>
                    <span class="font-semibold text-right text-custom-blue">{{ $queue->establishment->name }}</span>
                </div>
            </div>
            <div class="py-4 border-b border-gray-200">
                <div class="flex items-center justify-between text-lg">
                    <span class="text-gray-600">Type</span>
                    <span class="font-semibold text-gray-800">Banque</span>
                </div>
            </div>
            {{-- <div class="py-4 border-b border-gray-200">
                <div class="flex items-center justify-between text-lg">
                    <span class="text-gray-600">File</span>
                    <span class="font-semibold text-gray-800">{{ $queue->name }}</span>
                </div>
            </div> --}}
            <div class="py-4 border-b border-gray-200">
                <div class="flex items-center justify-between text-lg">
                    <span class="text-gray-600">Statut</span>
                    <span class="{{ $queue->is_active ? 'status-badge-active' : 'status-badge-closed' }}">
                        {{ $queue->is_active ? 'File active' : 'File fermée' }}
                    </span>
                </div>
            </div>
            <div class="py-4">
                <div class="flex items-center justify-between text-lg">
                    <span class="text-gray-600">En attente</span>
                    <span class="font-semibold text-gray-800">{{ $waitingTicketsCount }} utilisateurs</span>
                </div>
            </div>
        </div>

        <form action="{{ route('public.queue.join', $queue) }}" method="POST" class="mt-8">
            @csrf
            <button type="submit" class="w-full gradient-button">
                Rejoindre la file
            </button>
        </form>
    </div>

    <footer class="mt-8 mb-4 text-sm text-center text-gray-500">
        © {{ date('Y') }} VirtualQ - Tous droits réservés
    </footer>
</body>
</html>
