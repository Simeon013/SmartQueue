@extends('layouts.admin')

@section('header', 'Tableau de bord Agent')

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Bonjour, {{ $user->name }} !
                </h1>
                <p class="text-gray-600 mt-2">
                    Votre espace de travail virtuel
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    {{ str_replace('_', ' ', $user->role->value) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Mes tickets en cours -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">Mes tickets</h3>
                    <p class="text-2xl font-semibold text-gray-900">
                        {{ $currentTickets->count() }}
                        <span class="text-sm font-normal text-gray-500">en cours</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Prochain client -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full {{ $nextTicket ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">Prochain client</h3>
                    @if($nextTicket)
                        <p class="text-xl font-semibold text-gray-900">
                            {{ $nextTicket->queue->name }} - #{{ $nextTicket->ticket_number }}
                        </p>
                        <p class="text-sm text-gray-500">
                            {{ $nextTicket->created_at->diffForHumans() }}
                        </p>
                    @else
                        <p class="text-gray-500">Aucun ticket en attente</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Temps moyen d'attente -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">Temps moyen</h3>
                    @if(isset($stats['average_processing_time']) && $stats['average_processing_time'] > 0)
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ gmdate('i\m s\s', $stats['average_processing_time']) }}
                        </p>
                        <p class="text-sm text-gray-500">par ticket</p>
                    @else
                        <p class="text-gray-500">Données insuffisantes</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Section des files d'attente assignées -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Mes files d'attente</h2>
        </div>
        <div class="px-6 py-4">
            @if($assignedQueues->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($assignedQueues as $queue)
                        <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <h3 class="font-medium text-gray-900">{{ $queue->name }}</h3>
                                <span class="px-2 py-1 text-xs rounded-full {{ $queue->status === 'open' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $queue->status === 'open' ? 'Ouverte' : 'Fermée' }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">{{ $queue->service->name ?? 'Sans service' }}</p>
                            <div class="mt-2 flex justify-between items-center">
                                <span class="text-xs text-gray-500">
                                    {{ $queue->tickets()->where('status', 'waiting')->count() }} en attente
                                </span>
                                @if($queue->permission_type)
                                    <span class="text-xs px-2 py-1 rounded-full {{ $queue->permission_type === 'manager' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $queue->permission_type === 'manager' ? 'Gestionnaire' : 'Opérateur' }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-8">
                    Aucune file d'attente assignée pour le moment.
                </p>
            @endif
        </div>
    </div>

    <!-- Section des tickets en cours -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-medium text-gray-900">Mes tickets en cours</h2>
            <a href="{{ route('admin.queues.tickets.index', ['queue' => $assignedQueues->first()?->id ?? 0]) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                Voir tout
            </a>
        </div>
        <div class="px-6 py-4">
            @if($currentTickets->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File d'attente</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Depuis</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($currentTickets as $ticket)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">#{{ $ticket->ticket_number }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $ticket->queue->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $ticket->queue->service->name ?? 'Sans service' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($ticket->status === 'processing')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                En traitement
                                            </span>
                                        @elseif($ticket->status === 'called')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                Appelé
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $ticket->updated_at->diffForHumans() }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-8">
                    Aucun ticket en cours pour le moment.
                </p>
            @endif
        </div>
    </div>

    <!-- Section des statistiques personnelles -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Mes statistiques</h2>
        </div>
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-3xl font-bold text-gray-900">
                        {{ $stats['tickets_today'] ?? 0 }}
                    </p>
                    <p class="text-sm text-gray-500">Traités aujourd'hui</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-3xl font-bold text-gray-900">
                        {{ $stats['tickets_week'] ?? 0 }}
                    </p>
                    <p class="text-sm text-gray-500">Cette semaine</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    @if(isset($stats['average_processing_time']) && $stats['average_processing_time'] > 0)
                        <p class="text-3xl font-bold text-gray-900">
                            {{ gmdate('i\m s\s', $stats['average_processing_time']) }}
                        </p>
                        <p class="text-sm text-gray-500">Temps moyen par ticket</p>
                    @else
                        <p class="text-gray-500">Données insuffisantes</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
