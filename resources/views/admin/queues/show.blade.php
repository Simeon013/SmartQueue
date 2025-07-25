@extends('layouts.admin')

@php
    use SimpleSoftwareIO\QrCode\Facades\QrCode;
    
    $user = auth()->user();
    $canManage = $user->hasRole('super-admin') || 
                $user->hasRole('admin') || 
                $queue->userCanManage($user);
    $canDelete = $user->hasRole('super-admin') || 
                $user->hasRole('admin') || 
                $queue->userOwns($user);
    
    // Définition des couleurs et icônes pour chaque statut
    $statusConfig = [
        'open' => [
            'bg' => 'bg-green-100',
            'text' => 'text-green-800',
            'icon' => 'fa-play-circle',
            'label' => 'Ouverte',
            'indicator' => [
                'bg' => 'bg-green-100',
                'text' => 'text-green-800',
                'icon' => 'fa-check-circle',
                'message' => 'La file est active et en cours d\'utilisation'
            ]
        ],
        'paused' => [
            'bg' => 'bg-yellow-100',
            'text' => 'text-yellow-800',
            'icon' => 'fa-pause-circle',
            'label' => 'En pause',
            'indicator' => [
                'bg' => 'bg-yellow-100',
                'text' => 'text-yellow-800',
                'icon' => 'fa-pause-circle',
                'message' => 'La file est actuellement en pause'
            ]
        ],
        'blocked' => [
            'bg' => 'bg-orange-100',
            'text' => 'text-orange-800',
            'icon' => 'fa-ban',
            'label' => 'Bloquée',
            'indicator' => [
                'bg' => 'bg-orange-100',
                'text' => 'text-orange-800',
                'icon' => 'fa-ban',
                'message' => 'La file est actuellement bloquée'
            ]
        ],
        'closed' => [
            'bg' => 'bg-gray-100',
            'text' => 'text-gray-800',
            'icon' => 'fa-times-circle',
            'label' => 'Fermée',
            'indicator' => [
                'bg' => 'bg-gray-100',
                'text' => 'text-gray-800',
                'icon' => 'fa-lock',
                'message' => 'La file est fermée et ne peut plus être modifiée'
            ]
        ]
    ];
    
    $status = $queue->status->value; // Convertir l'énumération en chaîne
    $statusInfo = $statusConfig[$status] ?? $statusConfig['open'];
@endphp

@section('header', $queue->name)

@section('content')
    <!-- En-tête amélioré -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex flex-col space-y-4 md:space-y-0 md:flex-row md:items-center md:justify-between">
                <!-- Titre et informations -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl truncate">
                            {{ $queue->name }}
                        </h1>
                        <span class="ml-3 px-3 py-1 rounded-full text-xs font-semibold leading-5
                            {{ $statusInfo['bg'] }} {{ $statusInfo['text'] }}
                            transform transition-all duration-200 hover:scale-105">
                            <i class="fas {{ $statusInfo['icon'] }} mr-1"></i>
                            {{ $statusInfo['label'] }}
                        </span>
                    </div>
                    
                    <div class="mt-2 flex flex-col sm:flex-row sm:flex-wrap sm:space-x-6">
                        <div class="flex items-center text-sm text-gray-500 mb-2 sm:mb-0">
                            <i class="fas fa-calendar-alt mr-1.5 text-gray-400"></i>
                            Créée le {{ $queue->created_at->format('d/m/Y') }}
                        </div>
                        @if($queue->creator)
                        <div class="flex items-center text-sm text-gray-500">
                            <i class="fas fa-user mr-1.5 text-gray-400"></i>
                            Par {{ $queue->creator->name }}
                        </div>
                        @endif
                    </div>
                </div>
    
                <!-- Actions -->
                <div class="mt-4 flex flex-shrink-0 space-x-3 md:mt-0">
                    @if($canManage)
                        <a href="{{ route('admin.queues.edit', $queue) }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors duration-200">
                            <i class="fas fa-edit mr-2"></i>
                            <span>Modifier la file</span>
                        </a>
                        
                        <a href="{{ route('admin.queues.permissions', $queue) }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            <i class="fas fa-users-cog mr-2"></i>
                            <span>Gérer les accès</span>
                        </a>
                    @endif
                    
                    @if($canDelete)
                        <form action="{{ route('admin.queues.destroy', $queue) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200"
                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette file ? Cette action est irréversible.')">
                                <i class="fas fa-trash-alt mr-2"></i>
                                <span>Supprimer la file</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    
    <!-- Paramètres rapides de la file -->
    <div class="mt-8 bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Paramètres rapides</h3>
            <div class="mt-5">
                <div class="flex flex-col space-y-3 sm:flex-row sm:space-y-0 sm:space-x-3">
                    @if($canManage)
                        @if($status !== 'closed')
                            <!-- Bouton Basculer statut (Open/Blocked) -->
                            @if($status === 'blocked' || $status === 'open')
                            <form action="{{ route('admin.queues.toggle-status', $queue) }}" method="POST" class="flex-1">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent font-medium rounded-md shadow-sm text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:text-sm
                                    {{ $status === 'blocked' ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500' : 'bg-orange-600 hover:bg-orange-700 focus:ring-orange-500' }}">
                                    @if($status === 'blocked')
                                        <i class="fas fa-unlock mr-2"></i>
                                        Débloquer la file
                                    @else
                                        <i class="fas fa-ban mr-2"></i>
                                        Bloquer la file
                                    @endif
                                </button>
                            </form>
                            @else
                            <div class="flex-1"></div>
                            @endif

                            <!-- Bouton Mettre en pause/Reprendre -->
                            @if($status === 'open' || $status === 'paused')
                            <form action="{{ route('admin.queues.toggle-pause', $queue) }}" method="POST" class="flex-1">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent font-medium rounded-md shadow-sm text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:text-sm
                                    {{ $status === 'paused' ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500' : 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500' }}">
                                    @if($status === 'paused')
                                        <i class="fas fa-play mr-2"></i>
                                        Reprendre la file
                                    @else
                                        <i class="fas fa-pause mr-2"></i>
                                        Mettre en pause
                                    @endif
                                </button>
                            </form>
                            @else
                            <div class="flex-1"></div>
                            @endif

                            <!-- Bouton Fermer la file -->
                            @if($status !== 'closed')
                            <form action="{{ route('admin.queues.close', $queue) }}" method="POST" class="flex-1">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent font-medium rounded-md shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:text-sm"
                                    onclick="return confirm('Êtes-vous sûr de vouloir fermer cette file ? Cela annulera tous les tickets en attente.')">
                                    <i class="fas fa-lock mr-2"></i>
                                    Fermer la file
                                </button>
                            </form>
                            @endif
                        @else
                            <!-- Si la file est fermée, afficher un bouton pour la rouvrir -->
                            <form action="{{ route('admin.queues.reopen', $queue) }}" method="POST" class="flex-1">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:text-sm">
                                    <i class="fas fa-unlock mr-2"></i>
                                    Rouvrir la file
                                </button>
                            </form>
                            <div class="flex-1"></div>
                            <div class="flex-1"></div>
                        @endif
                    @else
                        <!-- Si l'utilisateur ne peut pas gérer la file -->
                        <div class="flex-1">
                            <span class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent font-medium rounded-md shadow-sm text-white bg-gray-400 cursor-not-allowed sm:text-sm">
                                <i class="fas fa-ban mr-2"></i>
                                Actions non autorisées
                            </span>
                        </div>
                    @endif
                </div>
                
                <!-- Indicateur d'état -->
                <div class="mt-4">
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusInfo['indicator']['bg'] }} {{ $statusInfo['indicator']['text'] }}">
                        <i class="fas {{ $statusInfo['indicator']['icon'] }} mr-1.5"></i>
                        {{ $statusInfo['indicator']['message'] }}
                    </div>
                    
                    @if($status === 'closed' && $queue->tickets()->where('status', 'waiting')->exists())
                    <div class="mt-2 text-sm text-yellow-700 bg-yellow-50 p-2 rounded-md">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Il reste des tickets en attente dans cette file. Voulez-vous les annuler ?
                        <form action="{{ route('admin.queues.cancel-pending-tickets', $queue) }}" method="POST" class="inline ml-2">
                            @csrf
                            <button type="submit" class="text-yellow-700 hover:text-yellow-900 font-medium"
                                onclick="return confirm('Êtes-vous sûr de vouloir annuler tous les tickets en attente ?')">
                                Annuler les tickets
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Espacement avant la section suivante -->
    <div class="mt-8"></div>

    <livewire:admin.queue-tickets :queue="$queue" />
@endsection
