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
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        {{ $queue->name }}
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            {{ $queue->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $queue->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </h2>
                    <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
                        <div class="mt-2 flex items-center text-sm text-gray-500">
                            <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                            </svg>
                            Créée le {{ $queue->created_at->format('d/m/Y') }}
                        </div>
                        @if($queue->creator)
                        <div class="mt-2 flex items-center text-sm text-gray-500">
                            <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                            Par {{ $queue->creator->name }}
                        </div>
                        @endif
                    </div>
                </div>
    
                <div class="mt-4 flex md:mt-0 space-x-3">
                    @if($canManage)
                        <a href="{{ route('admin.queues.edit', $queue) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            </svg>
                            Modifier
                        </a>
                        <a href="{{ route('admin.queues.permissions', $queue) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0116 8h1a1 1 0 011 1v1a1 1 0 01-1 1h-1a7 7 0 00-7 7 1 1 0 01-1 1h-1a1 1 0 01-1-1v-1a1 1 0 00-1-1H5a1 1 0 01-1-1v-1a1 1 0 00-1-1H2a1 1 0 01-1-1v-1a1 1 0 011-1h1a7 7 0 007-7 1 1 0 00-1-1H7a1 1 0 01-1-1V5a1 1 0 011-1h1a5 5 0 0110 0z" />
                            </svg>
                            Accès
                        </a>
                    @endif
                    
                    @if($canDelete)
                        <form action="{{ route('admin.queues.destroy', $queue) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette file ?')">
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                Supprimer
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
