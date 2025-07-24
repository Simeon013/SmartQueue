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
                            {{ $queue->is_active 
                                ? 'bg-green-100 text-green-800' 
                                : 'bg-red-100 text-red-800' }}
                            transform transition-all duration-200 hover:scale-105">
                            <i class="fas {{ $queue->is_active ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                            {{ $queue->is_active ? 'Active' : 'Inactive' }}
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
                    <!-- Bouton Activer/Désactiver -->
                    <form action="{{ route('admin.queues.toggle-status', $queue) }}" method="POST" class="flex-1">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent font-medium rounded-md shadow-sm text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:text-sm
                            {{ $queue->is_active ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500' : 'bg-green-600 hover:bg-green-700 focus:ring-green-500' }}">
                            @if($queue->is_active)
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                </svg>
                                Désactiver la file
                            @else
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                </svg>
                                Activer la file
                            @endif
                        </button>
                    </form>

                    <!-- Bouton Mettre en pause/Reprendre -->
                    <form action="{{ route('admin.queues.toggle-pause', $queue) }}" method="POST" class="flex-1">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:text-sm">
                            @if($queue->is_paused)
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M6.3 2.841A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z" />
                                </svg>
                                Reprendre la file
                            @else
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M5.75 3a.75.75 0 00-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 00.75-.75V3.75A.75.75 0 007.25 3h-1.5zM12.75 3a.75.75 0 00-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 00.75-.75V3.75a.75.75 0 00-.75-.75h-1.5z" />
                                </svg>
                                Mettre en pause
                            @endif
                        </button>
                    </form>

                    <!-- Bouton Fermer la file -->
                    <form action="{{ route('admin.queues.close', $queue) }}" method="POST" class="flex-1">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm"
                                onclick="return confirm('Êtes-vous sûr de vouloir fermer cette file ? Les tickets en attente seront annulés.')">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                            </svg>
                            Fermer la file
                        </button>
                    </form>
                </div>
                
                <!-- Indicateur d'état -->
                <div class="mt-4 text-sm text-gray-500">
                    @if($queue->is_paused)
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                            <svg class="-ml-1 mr-1.5 h-2 w-2 text-yellow-400" fill="currentColor" viewBox="0 0 8 8">
                                <circle cx="4" cy="4" r="3" />
                            </svg>
                            La file est actuellement en pause
                        </div>
                    @elseif(!$queue->is_active)
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                            <svg class="-ml-1 mr-1.5 h-2 w-2 text-red-400" fill="currentColor" viewBox="0 0 8 8">
                                <circle cx="4" cy="4" r="3" />
                            </svg>
                            La file est actuellement désactivée
                        </div>
                    @else
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <svg class="-ml-1 mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8">
                                <circle cx="4" cy="4" r="3" />
                            </svg>
                            La file est active et en cours d'utilisation
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
