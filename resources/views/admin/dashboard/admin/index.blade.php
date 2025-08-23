@extends('layouts.admin')

@section('header', 'Tableau de bord Administrateur')

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Bonjour, {{ $user->name }} 👋</h1>
                <p class="text-gray-500 mt-1">Gestion des files d'attente et des agents</p>
            </div>
            <div class="mt-4 md:mt-0">
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-purple-50 text-purple-700">
                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Administrateur
                </span>
            </div>
        </div>
    </div>

    <!-- Statistiques principales -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Carte Files d'attente -->
        <div class="bg-white rounded-lg border border-gray-200 p-5 transition-all duration-200 hover:shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Files d'attente</p>
                    <div class="flex items-baseline space-x-2">
                        <span class="text-2xl font-semibold text-gray-900">{{ $stats['active_queues'] ?? 0 }}</span>
                        <span class="text-sm text-gray-500">/ {{ $stats['total_queues'] ?? 0 }} total</span>
                    </div>
                </div>
                <div class="p-2 rounded-lg bg-blue-50 text-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <div class="flex justify-between text-sm">
                    <div class="text-center">
                        <div class="font-medium text-gray-900">{{ $stats['active_queues'] ?? 0 }}</div>
                        <div class="text-gray-500">Actives</div>
                    </div>
                    <div class="text-center">
                        <div class="font-medium text-gray-900">{{ ($stats['total_queues'] ?? 0) - ($stats['active_queues'] ?? 0) }}</div>
                        <div class="text-gray-500">Inactives</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Carte Tickets -->
        <div class="bg-white rounded-lg border border-gray-200 p-5 transition-all duration-200 hover:shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Tickets</p>
                    <div class="flex items-baseline space-x-2">
                        <span class="text-2xl font-semibold text-gray-900">{{ $stats['pending_tickets'] ?? 0 }}</span>
                        <span class="text-sm text-gray-500">en attente</span>
                    </div>
                </div>
                <div class="p-2 rounded-lg bg-yellow-50 text-yellow-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <div class="flex justify-between text-sm">
                    <div class="text-center">
                        <div class="font-medium text-gray-900">{{ $stats['pending_tickets'] ?? 0 }}</div>
                        <div class="text-gray-500">En attente</div>
                    </div>
                    <div class="text-center">
                        <div class="font-medium text-gray-900">0</div>
                        <div class="text-gray-500">Aujourd'hui</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Carte Agents -->
        <div class="bg-white rounded-lg border border-gray-200 p-5 transition-all duration-200 hover:shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Agents</p>
                    <div class="flex items-baseline space-x-2">
                        <span class="text-2xl font-semibold text-gray-900">{{ $stats['active_agents'] ?? 0 }}</span>
                        <span class="text-sm text-gray-500">actifs</span>
                    </div>
                </div>
                <div class="p-2 rounded-lg bg-green-50 text-green-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <div class="flex justify-between text-sm">
                    <div class="text-center">
                        <div class="font-medium text-gray-900">{{ $stats['active_agents'] ?? 0 }}</div>
                        <div class="text-gray-500">Actifs</div>
                    </div>
                    <div class="text-center">
                        <div class="font-medium text-gray-900">0</div>
                        <div class="text-gray-500">Occupés</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Files d'attente -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h2 class="text-lg font-medium text-gray-900">Files d'attente</h2>
            <a href="{{ route('admin.queues.create') }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="-ml-0.5 mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Nouvelle file d'attente
            </a>
        </div>

        @if($allQueues->isNotEmpty())
            <div class="divide-y divide-gray-200">
                @foreach($allQueues as $queue)
                    <div class="p-4 hover:bg-gray-50 transition-colors duration-150">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="p-2 rounded-lg {{ $queue->status === 'open' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <h3 class="text-sm font-medium text-gray-900">{{ $queue->name }}</h3>
                                        @if($queue->permission_type)
                                            @if($queue->permission_type === 'owner')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Propriétaire
                                                </span>
                                            @elseif($queue->permission_type === 'manager')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Gestionnaire
                                                </span>
                                            @elseif($queue->permission_type === 'operator')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                    Opérateur
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-500">
                                        Service: {{ $queue->service->name ?? 'Aucun service' }}
                                        @if($queue->created_by === auth()->id())
                                            <span class="text-xs text-gray-400 ml-2">(Créée par vous)</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="text-center">
                                    <span class="text-sm font-medium text-gray-900">{{ $queue->pending_tickets_count }}</span>
                                    <p class="text-xs text-gray-500">En attente</p>
                                </div>
                                <div class="text-center">
                                    <span class="text-sm font-medium text-gray-900">{{ $queue->tickets_count }}</span>
                                    <p class="text-xs text-gray-500">Total</p>
                                </div>
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.queues.show', $queue) }}" class="text-blue-600 hover:text-blue-800 p-1 rounded-full hover:bg-blue-50" title="Voir">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    @if($queue->access_level === 'owner' || $queue->access_level === 'edit')
                                        <a href="{{ route('admin.queues.edit', $queue) }}" class="text-indigo-600 hover:text-indigo-800 p-1 rounded-full hover:bg-indigo-50" title="Modifier">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune file d'attente</h3>
                <p class="mt-1 text-sm text-gray-500">Vous n'avez accès à aucune file d'attente pour le moment.</p>
                <div class="mt-6">
                    <a href="{{ route('admin.queues.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Créer une file d'attente
                    </a>
                </div>
            </div>
        @endif
    </div>
    </div>

    <!-- Tickets traités -->
    <div class="bg-white rounded-lg border border-gray-200 mt-6 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h2 class="text-lg font-medium text-gray-900">Derniers tickets traités</h2>
            @if($allQueues->isNotEmpty())
                <a href="{{ route('admin.queues.tickets.index', $allQueues->first()) }}" class="text-sm text-blue-600 hover:text-blue-800">
                    Voir tout
                </a>
            @endif
        </div>
        <div class="divide-y divide-gray-200">

            @if($recentTickets->isNotEmpty())
                @foreach($recentTickets as $ticket)
                    <div class="p-4 hover:bg-gray-50 transition-colors duration-150">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="p-2 rounded-lg {{ $ticket->status === 'waiting' ? 'bg-yellow-100 text-yellow-600' : ($ticket->status === 'processing' ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-600') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900">Ticket #{{ $ticket->code_ticket }}</h3>
                                    <p class="text-sm text-gray-500">
                                        {{ $ticket->queue->name ?? 'File inconnue' }} •
                                        <span class="font-medium">par {{ $ticket->handler->name }}</span>
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 inline-block -mt-0.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ $ticket->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                @if($ticket->status === 'waiting')
                                    <span class="px-2.5 py-0.5 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                        En attente
                                    </span>
                                @elseif($ticket->status === 'processing')
                                    <span class="px-2.5 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                        En cours
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                        Traité
                                    </span>
                                @endif

                                <a href="#" class="p-1.5 text-gray-400 hover:text-blue-600 rounded-full hover:bg-blue-50 transition-colors duration-150">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun ticket récent</h3>
                    <p class="mt-1 text-sm text-gray-500">Les nouveaux tickets apparaîtront ici.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
