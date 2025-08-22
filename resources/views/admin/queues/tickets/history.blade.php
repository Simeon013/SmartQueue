@extends('layouts.admin')

@push('styles')
<style>
    .fade-enter-active, .fade-leave-active {
        transition: opacity 0.2s;
    }
    .fade-enter, .fade-leave-to {
        opacity: 0;
    }
    .cursor-pointer {
        cursor: pointer;
    }
    .status-badge {
        @apply px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full;
    }
    .status-waiting { @apply bg-blue-100 text-blue-800; }
    .status-paused { @apply bg-yellow-100 text-yellow-800; }
    .status-in_progress { @apply bg-purple-100 text-purple-800; }
    .status-served { @apply bg-green-100 text-green-800; }
    .status-skipped { @apply bg-orange-100 text-orange-800; }
    .status-cancelled { @apply bg-red-100 text-red-800; }
</style>
@endpush

@section('content')
    <div class="px-4 py-8 sm:px-6 lg:px-8">
        <!-- En-tête avec statistiques -->
        <div class="mb-8">
            <div class="flex flex-col gap-4 justify-between items-start sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Historique des tickets
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        File d'attente : {{ $queue->name }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ url()->previous() }}"
                       class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-md border border-gray-300 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="mr-2 fas fa-arrow-left"></i> Retour
                    </a>
                    {{-- <button onclick="window.print()"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-md border border-gray-300 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 print:hidden">
                        <i class="mr-2 fas fa-print"></i> Imprimer
                    </button> --}}
                </div>
            </div>

            <!-- Statistiques rapides -->
            <div class="grid grid-cols-1 gap-5 mt-6 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total des tickets -->
                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 bg-indigo-500 rounded-md">
                                <i class="text-white fas fa-ticket-alt"></i>
                            </div>
                            <div class="flex-1 ml-5 w-0">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total des tickets</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">{{ $stats['total'] }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tickets en attente -->
                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 bg-blue-500 rounded-md">
                                <i class="text-white fas fa-clock"></i>
                            </div>
                            <div class="flex-1 ml-5 w-0">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">En attente</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">{{ $stats['waiting'] }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tickets en cours -->
                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 bg-purple-500 rounded-md">
                                <i class="text-white fas fa-spinner"></i>
                            </div>
                            <div class="flex-1 ml-5 w-0">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">En cours</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">{{ $stats['in_progress'] }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tickets servis -->
                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 bg-green-500 rounded-md">
                                <i class="text-white fas fa-check-circle"></i>
                            </div>
                            <div class="flex-1 ml-5 w-0">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Servis</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">{{ $stats['served'] }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tickets passés -->
                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 bg-orange-500 rounded-md">
                                <i class="text-white fas fa-forward"></i>
                            </div>
                            <div class="flex-1 ml-5 w-0">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Passés</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">{{ $stats['skipped'] }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tickets annulés -->
                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 bg-red-500 rounded-md">
                                <i class="text-white fas fa-times-circle"></i>
                            </div>
                            <div class="flex-1 ml-5 w-0">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Annulés</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">{{ $stats['cancelled'] }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tickets en pause -->
                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 bg-yellow-500 rounded-md">
                                <i class="text-white fas fa-pause"></i>
                            </div>
                            <div class="flex-1 ml-5 w-0">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">En pause</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">{{ $stats['paused'] }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Temps moyen de traitement -->
                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 bg-cyan-500 rounded-md">
                                <i class="text-white fas fa-stopwatch"></i>
                            </div>
                            <div class="flex-1 ml-5 w-0">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Temps moyen</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            {{ $stats['avg_processing_time'] ?? '-' }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres avancés -->
        <div class="overflow-hidden mb-6 bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Filtres</h3>
            </div>
            <div class="px-4 py-5 border-t border-gray-200 sm:p-6">
                <form method="GET" action="{{ route('admin.queues.tickets.history', $queue) }}">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <!-- Filtre par statut -->
                        <!-- Filtre par statut -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Statut</label>
                            <select id="status" name="status" class="block py-2 pr-10 pl-3 mt-1 w-full text-base rounded-md border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Tous les statuts</option>
                                <option value="waiting" {{ request('status') === 'waiting' ? 'selected' : '' }}>En attente</option>
                                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En cours</option>
                                <option value="served" {{ request('status') === 'served' ? 'selected' : '' }}>Servi</option>
                                <option value="skipped" {{ request('status') === 'skipped' ? 'selected' : '' }}>Passé</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulé</option>
                                <option value="paused" {{ request('status') === 'paused' ? 'selected' : '' }}>En pause</option>
                            </select>
                        </div>

                        <!-- Filtre par date -->
                        <div>
                            <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" id="date" name="date" value="{{ request('date') }}" class="block py-2 pr-10 pl-3 mt-1 w-full text-base rounded-md border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>

                        <!-- Recherche -->
                        <div class="sm:col-span-2">
                            <label for="search" class="block text-sm font-medium text-gray-700">Rechercher</label>
                            <div class="relative mt-1 rounded-md shadow-sm">
                                <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Rechercher par code ou numéro..." class="block pr-12 pl-4 w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <div class="flex absolute inset-y-0 right-0 items-center pr-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between mt-5">
                        <!-- Bouton de réinitialisation -->
                        <a href="{{ route('admin.queues.tickets.history', $queue) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-md border border-gray-300 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Réinitialiser les filtres
                        </a>

                        <!-- Bouton d'application des filtres -->
                        <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md border border-transparent shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Appliquer les filtres
                        </button>
                    </div>
                </form>

                @if(request()->hasAny(['status', 'date', 'search']))
                    <div class="p-4 mt-6 mb-6 bg-indigo-50 rounded-lg">
                        <div class="flex flex-wrap gap-2 items-center">
                            <span class="text-sm font-medium text-gray-700">Filtres actifs :</span>
                            @if(request('status'))
                                <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium text-indigo-800 bg-indigo-100 rounded-full">
                                    Statut: {{ [
                                        'waiting' => 'En attente',
                                        'in_progress' => 'En cours',
                                        'served' => 'Servi',
                                        'skipped' => 'Passé',
                                        'cancelled' => 'Annulé',
                                        'paused' => 'En pause'
                                    ][request('status')] ?? request('status') }}
                                    <a href="{{ request()->fullUrlWithQuery(['status' => null]) }}" class="inline-flex ml-1.5 text-indigo-500 focus:outline-none">
                                        <span class="sr-only">Supprimer le filtre</span>
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    </a>
                                </span>
                            @endif
                            @if(request('date'))
                                <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium text-indigo-800 bg-indigo-100 rounded-full">
                                    Date: {{ \Carbon\Carbon::parse(request('date'))->format('d/m/Y') }}
                                    <a href="{{ request()->fullUrlWithQuery(['date' => null]) }}" class="inline-flex ml-1.5 text-indigo-500 focus:outline-none">
                                        <span class="sr-only">Supprimer le filtre</span>
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    </a>
                                </span>
                            @endif
                            @if(request('search'))
                                <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium text-indigo-800 bg-indigo-100 rounded-full">
                                    Recherche: {{ request('search') }}
                                    <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="inline-flex ml-1.5 text-indigo-500 focus:outline-none">
                                        <span class="sr-only">Supprimer le filtre</span>
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    </a>
                                </span>
                            @endif
                            <a href="{{ route('admin.queues.tickets.history', $queue) }}" class="ml-auto text-sm font-medium text-indigo-600 hover:text-indigo-500">
                                Réinitialiser les filtres
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Tableau des tickets -->
        <div class="overflow-hidden bg-white shadow sm:rounded-lg">
            <div class="flex flex-col">
                <div class="overflow-x-auto -my-2 sm:-mx-6 lg:-mx-8">
                    <div class="inline-block py-2 min-w-full align-middle sm:px-6 lg:px-8">
                        <div class="overflow-hidden border-b border-gray-200 shadow sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr class="divide-x divide-gray-200">
                                        @php
                                            $sortField = request('sort', 'created_at');
                                            $sortDirection = request('direction', 'desc');

                                            $columns = [
                                                'code_ticket' => 'Code',
                                                'status' => 'Statut',
                                                'created_at' => 'Créé le',
                                                'handler_id' => 'Agent',
                                                'processed_at' => 'Traitement',
                                                'processing_time' => 'Durée'
                                            ];
                                        @endphp

                                        @foreach($columns as $field => $label)
                                            @php
                                                // Déterminer la nouvelle direction de tri
                                                $newDirection = ($sortField === $field && $sortDirection === 'asc') ? 'desc' : 'asc';

                                                // Générer l'URL avec les paramètres de tri mis à jour
                                                $sortUrl = request()->fullUrlWithQuery([
                                                    'sort' => $field,
                                                    'direction' => $newDirection,
                                                    'page' => 1 // Revenir à la première page lors du tri
                                                ]);

                                                // Déterminer l'icône à afficher
                                                $sortIcon = '';
                                                if ($sortField === $field) {
                                                    $sortIcon = $sortDirection === 'asc'
                                                        ? '<i class="ml-1.5 text-indigo-600 fas fa-sort-up"></i>'
                                                        : '<i class="ml-1.5 text-indigo-600 fas fa-sort-down"></i>';
                                                } else {
                                                    $sortIcon = '<i class="ml-1.5 text-gray-300 fas fa-sort"></i>';
                                                }
                                            @endphp

                                            @if(in_array($field, ['code_ticket', 'number', 'status', 'created_at', 'processed_at', 'processing_time']))
                                                <th scope="col" class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                    <a href="{{ $sortUrl }}" class="flex items-center p-1 -m-1 rounded hover:bg-gray-100">
                                                        <span>{{ $label }}</span>
                                                        {!! $sortIcon !!}
                                                    </a>
                                                </th>
                                            @else
                                                <th scope="col" class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                    {{ $label }}
                                                </th>
                                            @endif
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($tickets as $ticket)
                                        <tr class="divide-x divide-gray-200 hover:bg-gray-50">
                                            <!-- Code Ticket -->
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="font-mono text-sm font-medium text-gray-900">
                                                    {{ $ticket->code_ticket }}
                                                </div>
                                            </td>

                                            <!-- Numéro -->
                                            {{-- <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    #{{ $ticket->number }}
                                                </div>
                                            </td> --}}

                                            <!-- Statut -->
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                @php
                                                    $statusConfig = [
                                                        'waiting' => ['icon' => 'clock', 'label' => 'En attente', 'color' => 'blue'],
                                                        'paused' => ['icon' => 'pause', 'label' => 'En pause', 'color' => 'yellow'],
                                                        'in_progress' => ['icon' => 'spinner', 'label' => 'En cours', 'color' => 'purple'],
                                                        'served' => ['icon' => 'check-circle', 'label' => 'Servi', 'color' => 'green'],
                                                        'skipped' => ['icon' => 'forward', 'label' => 'Passé', 'color' => 'orange'],
                                                        'cancelled' => ['icon' => 'times-circle', 'label' => 'Annulé', 'color' => 'red']
                                                    ][$ticket->status] ?? ['icon' => 'question-circle', 'label' => 'Inconnu', 'color' => 'gray'];
                                                @endphp
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $statusConfig['color'] }}-100 text-{{ $statusConfig['color'] }}-800">
                                                    <i class="fas fa-{{ $statusConfig['icon'] }} mr-1.5 {{ $ticket->status === 'in_progress' ? 'fa-spin' : '' }}"></i>
                                                    {{ $statusConfig['label'] }}
                                                </span>
                                            </td>

                                            <!-- Date de création -->
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    {{ $ticket->created_at->format('d/m/Y') }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $ticket->created_at->format('H:i:s') }}
                                                </div>
                                            </td>

                                            <!-- Agent -->
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    @if($ticket->handler)
                                                        <div class="flex items-center">
                                                            <span class="inline-block mr-2 w-2.5 h-2.5 bg-green-400 rounded-full"></span>
                                                            {{ $ticket->handler->name }}
                                                        </div>
                                                    @else
                                                        <span class="text-gray-400">-</span>
                                                    @endif
                                                </div>
                                            </td>

                                            <!-- Traitement -->
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="space-y-1 text-xs">
                                                    @if($ticket->called_at)
                                                        <div class="flex items-center">
                                                            <i class="mr-1.5 w-4 text-center text-indigo-500 fas fa-bell"></i>
                                                            <span>Appelé: {{ $ticket->called_at->format('H:i:s') }}</span>
                                                        </div>
                                                    @endif
                                                    @if($ticket->served_at)
                                                        <div class="flex items-center">
                                                            <i class="mr-1.5 w-4 text-center text-green-500 fas fa-check-circle"></i>
                                                            <span>Servi: {{ $ticket->served_at->format('H:i:s') }}</span>
                                                        </div>
                                                    @endif
                                                    @if($ticket->cancelled_at)
                                                        <div class="flex items-center">
                                                            <i class="mr-1.5 w-4 text-center text-red-500 fas fa-times-circle"></i>
                                                            <span>Annulé: {{ $ticket->cancelled_at->format('H:i:s') }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>

                                            <!-- Durée -->
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                @php
                                                    $duration = null;
                                                    $isActive = false;

                                                    if ($ticket->called_at && $ticket->served_at) {
                                                        $duration = $ticket->called_at->diffForHumans($ticket->served_at, true);
                                                    } elseif ($ticket->created_at && $ticket->status === 'waiting') {
                                                        $duration = $ticket->created_at->diffForHumans(now(), true);
                                                        $isActive = true;
                                                    } elseif ($ticket->status === 'in_progress' && $ticket->called_at) {
                                                        $duration = $ticket->called_at->diffForHumans(now(), true);
                                                        $isActive = true;
                                                    }
                                                @endphp

                                                @if($duration)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $isActive ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800' }}">
                                                        <i class="fas {{ $isActive ? 'fa-spin' : '' }} fa-{{ $isActive ? 'spinner' : 'clock' }} mr-1"></i>
                                                        {{ $duration }}
                                                    </span>
                                                @else
                                                    <span class="text-xs text-gray-500">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-6 py-4 text-sm text-center text-gray-500 whitespace-nowrap">
                                                Aucun ticket trouvé pour les critères sélectionnés
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($tickets->hasPages())
                            <div class="flex justify-between items-center px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
                                <div class="flex flex-1 justify-between sm:hidden">
                                    @if($tickets->onFirstPage())
                                        <span class="inline-flex relative items-center px-4 py-2 text-sm font-medium text-gray-300 bg-white rounded-md border border-gray-300">
                                            Précédent
                                        </span>
                                    @else
                                        <a href="{{ $tickets->previousPageUrl() }}" class="inline-flex relative items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-md border border-gray-300 hover:bg-gray-50">
                                            Précédent
                                        </a>
                                    @endif

                                    @if($tickets->hasMorePages())
                                        <a href="{{ $tickets->nextPageUrl() }}" class="inline-flex relative items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white rounded-md border border-gray-300 hover:bg-gray-50">
                                            Suivant
                                        </a>
                                    @else
                                        <span class="inline-flex relative items-center px-4 py-2 ml-3 text-sm font-medium text-gray-300 bg-white rounded-md border border-gray-300">
                                            Suivant
                                        </span>
                                    @endif
                                </div>

                                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm text-gray-700">
                                            Affichage de
                                            <span class="font-medium">{{ $tickets->firstItem() }}</span>
                                            à
                                            <span class="font-medium">{{ $tickets->lastItem() }}</span>
                                            sur
                                            <span class="font-medium">{{ $tickets->total() }}</span>
                                            résultats
                                        </p>
                                    </div>
                                    <div>
                                        <nav class="inline-flex relative z-0 -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                            <!-- Previous Page Link -->
                                            @if($tickets->onFirstPage())
                                                <span class="inline-flex relative items-center px-2 py-2 text-sm font-medium text-gray-300 bg-white rounded-l-md border border-gray-300">
                                                    <span class="sr-only">Précédent</span>
                                                    <i class="fas fa-chevron-left"></i>
                                                </span>
                                            @else
                                                <a href="{{ $tickets->previousPageUrl() }}" class="inline-flex relative items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white rounded-l-md border border-gray-300 hover:bg-gray-50">
                                                    <span class="sr-only">Précédent</span>
                                                    <i class="fas fa-chevron-left"></i>
                                                </a>
                                            @endif

                                            <!-- Pagination Elements -->
                                            @foreach($tickets->links()->elements[0] as $page => $url)
                                                @if($page == $tickets->currentPage())
                                                    <span class="inline-flex relative items-center px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 border border-indigo-500">
                                                        {{ $page }}
                                                    </span>
                                                @else
                                                    <a href="{{ $url }}" class="inline-flex relative items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">
                                                        {{ $page }}
                                                    </a>
                                                @endif
                                            @endforeach

                                            <!-- Next Page Link -->
                                            @if($tickets->hasMorePages())
                                                <a href="{{ $tickets->nextPageUrl() }}" class="inline-flex relative items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white rounded-r-md border border-gray-300 hover:bg-gray-50">
                                                    <span class="sr-only">Suivant</span>
                                                    <i class="fas fa-chevron-right"></i>
                                                </a>
                                            @else
                                                <span class="inline-flex relative items-center px-2 py-2 text-sm font-medium text-gray-300 bg-white rounded-r-md border border-gray-300">
                                                    <span class="sr-only">Suivant</span>
                                                    <i class="fas fa-chevron-right"></i>
                                                </span>
                                            @endif
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
