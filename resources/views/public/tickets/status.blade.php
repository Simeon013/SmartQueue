<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Votre Position - VirtualQ</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Custom styles for the position cards */
        .position-card {
            background-color: white;
            border-radius: 0.5rem; /* rounded-lg */
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            padding: 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .position-card-title {
            font-size: 0.75rem; /* text-xs */
            font-weight: 500; /* font-medium */
            color: #6B7280; /* text-gray-500 */
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .position-card-value {
            font-size: 1.875rem; /* text-3xl */
            font-weight: 700; /* font-bold */
            color: #1F2937; /* text-gray-900 */
        }

        .establishment-info-card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            border-left: 5px solid #22C55E; /* green-500 */
            padding: 1.5rem;
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
    </style>
</head>
<body class="font-sans bg-gray-100">
    <div class="flex flex-col min-h-screen">
        <!-- Navbar -->
        <nav class="flex items-center justify-between p-4 bg-white shadow">
            <div class="text-xl font-bold text-gray-800">VirtualQ</div>
            <button class="text-gray-500 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </nav>

        <!-- Main Content -->
        <main class="flex-grow p-4 space-y-6">
            <!-- Position Banner -->
            <div class="p-6 text-center text-white bg-blue-600 rounded-lg shadow-md">
                <h1 class="mb-2 text-2xl font-bold">Votre Position</h1>
                <p class="text-blue-200">Restez informé de votre progression dans la file</p>
            </div>

            <!-- Position Cards Grid -->
            <div class="grid grid-cols-2 gap-4">
                <div class="position-card">
                    <div class="position-card-title">Votre Numéro</div>
                    <div class="position-card-value">{{ $ticket->number }}</div>
                </div>
                <div class="position-card">
                    <div class="position-card-title">Position Actuelle</div>
                    <div class="position-card-value">{{ $position }}</div>
                </div>
                <div class="position-card">
                    <div class="position-card-title">Temps Estimé</div>
                    <div class="position-card-value">{{ $estimatedWaitTime }}</div>
                </div>
                <div class="position-card">
                    <div class="position-card-title">Statut de la File</div>
                    <div class="position-card-value">
                        <span class="{{ $queue->is_active ? 'status-badge-active' : 'status-badge-closed' }}">
                            {{ $queue->is_active ? 'Active' : 'Fermée' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Establishment Info Card -->
            <div class="establishment-info-card">
                <div class="flex items-center mb-4">
                    <svg class="w-6 h-6 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.828 0L6.343 16.657m11.314-11.314L12 3.5l-5.657 5.657m11.314 0a9 9 0 11-16 0 9 9 0 0116 0z"></path>
                    </svg>
                    <h2 class="text-xl font-bold text-gray-800">Informations établissement</h2>
                </div>
                <p class="mb-2"><span class="font-semibold">Établissement :</span> {{ $queue->establishment->name }}</p>
                <p class="mb-2"><span class="font-semibold">Type :</span> {{ $queue->name }}</p>
                <p class="mb-2"><span class="font-semibold">Numéro en cours :</span> {{ $currentServingNumber }}</p>
                <p><span class="font-semibold">Utilisateurs en attente :</span> {{ $waitingTicketsCount }}</p>
            </div>
        </main>

        <!-- Footer / Last Updated -->
        <footer class="flex items-center justify-center p-4 text-sm text-center text-gray-500">
            <svg class="w-4 h-4 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l3 3a1 1 0 001.414-1.414L11 9.586V6z" clip-rule="evenodd"></path>
            </svg>
            <svg class="w-4 h-4 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 0020 14c0 1.577-.417 3.075-1.146 4.382M4 12v-.75m0 0a8.001 8.001 0 0115.356-2A8.001 8.001 0 004 12zm0 0H3m-1 0h1m-1 0v-.75m0 0a8.001 8.001 0 0115.356-2A8.001 8.001 0 004 12z"></path>
            </svg>
            Dernière mise à jour : {{ date('H:i:s') }}
        </footer>
    </div>
</body>
</html> 