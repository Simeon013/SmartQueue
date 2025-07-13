@extends('layouts.admin')

@section('header', 'Gestion des accès - ' . $queue->name)

@if(session('success') && str_contains(session('success'), 'créée avec succès'))
    @push('styles')
    <style>
        .new-queue-notice {
            animation: slideInDown 0.5s ease-out;
        }
        
        @keyframes slideInDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
    @endpush
@endif

@push('styles')
<style>
    /* Animation pour les nouvelles files */
    .new-queue-notice {
        animation: slideInDown 0.5s ease-out;
    }
    
    @keyframes slideInDown {
        from {
            transform: translateY(-100%);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    /* Dropdown de permissions */
    .permission-dropdown {
        position: absolute;
        z-index: 50;
        margin-top: 0.5rem;
        background-color: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.375rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    
    /* Dropdowns flottants pour les permissions */
    [id^="permission-dropdown-"] {
        position: fixed !important;
        z-index: 9999 !important;
        background-color: white !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 0.375rem !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
        min-width: 200px !important;
    }
    
    /* Animation pour les dropdowns */
    .dropdown-enter {
        opacity: 0;
        transform: translateY(-10px);
    }
    
    .dropdown-enter-active {
        opacity: 1;
        transform: translateY(0);
        transition: opacity 200ms, transform 200ms;
    }
</style>
@endpush

@section('content')

@if(session('success') && str_contains(session('success'), 'créée avec succès'))
    <!-- Notice spéciale pour les nouvelles files -->
    <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-blue-50 border border-green-200 rounded-lg new-queue-notice">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
                            <div class="ml-3">
                    <h3 class="text-lg font-medium text-green-800">File créée avec succès !</h3>
                    <p class="mt-1 text-green-700">
                        Votre nouvelle file d'attente a été créée. Le mode "tous les agents - gestion complète" a été activé par défaut. Tous les agents peuvent maintenant gérer cette file.
                    </p>
                </div>
        </div>
    </div>
@endif

<div class="bg-white rounded-lg shadow">
    <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $queue->name }}</h2>
                    <p class="mt-1 text-gray-600">Code: {{ $queue->code }}</p>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $queue->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $queue->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="pb-6 mx-auto max-w-7xl sm:px-6 lg:px-8">

        <!-- Mode de gestion des permissions -->
        <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="mb-4 text-lg font-medium text-gray-900">Mode de gestion des accès</h3>
                
                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <!-- Option 1: Tous les agents - Gestion complète -->
                    <div class="border-2 rounded-lg p-6 {{ $currentMode === 'all_agents_manager' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center w-8 h-8 bg-blue-100 rounded-full">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 ml-4">
                                <h4 class="text-lg font-medium text-gray-900">Gestion complète</h4>
                                <p class="mt-1 text-gray-600">Tous les agents peuvent gérer la file et les tickets</p>
                                
                                @if($currentMode === 'all_agents_manager')
                                    <div class="mt-4">
                                        <span class="inline-flex items-center px-3 py-1 text-sm font-medium text-green-800 bg-green-100 rounded-full">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            Actif
                                        </span>
                                    </div>
                                @else
                                    <form method="POST" action="{{ route('admin.queues.permissions.mode', $queue) }}" class="mt-4">
                                        @csrf
                                        <input type="hidden" name="mode" value="all_agents_manager">
                                        <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Activer
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Option 2: Tous les agents - Gestion des tickets seulement -->
                    <div class="border-2 rounded-lg p-6 {{ $currentMode === 'all_agents_operator' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-gray-300' }}">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center w-8 h-8 bg-green-100 rounded-full">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 ml-4">
                                <h4 class="text-lg font-medium text-gray-900">Gestion des tickets</h4>
                                <p class="mt-1 text-gray-600">Tous les agents peuvent gérer les tickets seulement</p>
                                
                                @if($currentMode === 'all_agents_operator')
                                    <div class="mt-4">
                                        <span class="inline-flex items-center px-3 py-1 text-sm font-medium text-green-800 bg-green-100 rounded-full">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            Actif
                                        </span>
                                    </div>
                                @else
                                    <form method="POST" action="{{ route('admin.queues.permissions.mode', $queue) }}" class="mt-4">
                                        @csrf
                                        <input type="hidden" name="mode" value="all_agents_operator">
                                        <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            Activer
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Option 3: Agents sélectionnés -->
                    <div class="border-2 rounded-lg p-6 {{ $currentMode === 'selected_agents' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-gray-300' }}">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center w-8 h-8 bg-purple-100 rounded-full">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 ml-4">
                                <h4 class="text-lg font-medium text-gray-900">Agents sélectionnés</h4>
                                <p class="mt-1 text-gray-600">Choisissez précisément quels agents ont accès</p>
                                
                                @if($currentMode === 'selected_agents')
                                    <div class="mt-4">
                                        <span class="inline-flex items-center px-3 py-1 text-sm font-medium text-green-800 bg-green-100 rounded-full">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            Actif
                                        </span>
                                    </div>
                                @else
                                    <form method="POST" action="{{ route('admin.queues.permissions.mode', $queue) }}" class="mt-4">
                                        @csrf
                                        <input type="hidden" name="mode" value="selected_agents">
                                        <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-purple-600 border border-transparent rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                            Activer
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <!-- Modal pour ajouter des agents -->
        <div id="addAgentsModal" class="fixed inset-0 z-50 hidden w-full h-full overflow-y-auto bg-gray-600 bg-opacity-50">
            <div class="relative w-11/12 p-5 mx-auto bg-white border rounded-md shadow-lg top-20 md:w-3/4 lg:w-1/2">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Ajouter des agents</h3>
                        <button onclick="closeAddAgentsModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <form method="POST" action="{{ route('admin.queues.permissions.add-agents', $queue) }}" class="space-y-4">
                        @csrf
                        
                        <div>
                            <label for="modal_permission_type" class="block mb-2 text-sm font-medium text-gray-700">Type de permission</label>
                            <select id="modal_permission_type" name="permission_type" class="block w-full border-gray-300 rounded-md shadow-sm focus:border-purple-500 focus:ring-purple-500" required>
                                <option value="">Sélectionner un type</option>
                                <option value="manager">Gestionnaire (peut tout gérer)</option>
                                <option value="operator">Opérateur (peut gérer les tickets)</option>
                            </select>
                        </div>
                        
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium text-gray-700">Sélectionner les agents</label>
                                <div class="flex space-x-2">
                                    <button type="button" onclick="selectAllAgents()" class="px-2 py-1 text-xs text-purple-700 bg-purple-100 rounded hover:bg-purple-200">
                                        Tout sélectionner
                                    </button>
                                    <button type="button" onclick="deselectAllAgents()" class="px-2 py-1 text-xs text-gray-700 bg-gray-100 rounded hover:bg-gray-200">
                                        Tout désélectionner
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Barre de recherche dans la modal -->
                            <div class="mb-3">
                                <div class="relative">
                                    <input type="text" id="modalAgentSearch" placeholder="Rechercher un agent..." 
                                           class="block w-full py-2 pl-10 pr-3 leading-5 placeholder-gray-500 bg-white border border-gray-300 rounded-md focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-purple-500 focus:border-purple-500">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="p-3 overflow-y-auto border border-gray-300 rounded-md max-h-64 bg-gray-50">
                                @foreach($agents as $agent)
                                    @php
                                        $hasPermission = $queuePermissions->where('user_id', $agent->id)->first();
                                    @endphp
                                    
                                    @if(!$hasPermission)
                                    <label class="flex items-center px-3 py-3 space-x-3 transition-colors duration-150 border border-transparent rounded-lg cursor-pointer agent-option hover:bg-white hover:border-purple-200">
                                        <input type="checkbox" name="agent_ids[]" value="{{ $agent->id }}" 
                                               class="text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                        <div class="flex-shrink-0">
                                            <div class="flex items-center justify-center w-8 h-8 bg-purple-100 rounded-full">
                                                <span class="text-sm font-medium text-purple-700">
                                                    {{ strtoupper(substr($agent->name, 0, 2)) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-900 agent-name">{{ $agent->name }}</div>
                                            <div class="text-sm text-gray-500 agent-email">{{ $agent->email }}</div>
                                        </div>
                                    </label>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        
                        <div class="flex justify-end pt-4 space-x-3">
                            <button type="button" onclick="closeAddAgentsModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                Annuler
                            </button>
                            <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-purple-600 border border-transparent rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Ajouter les agents sélectionnés
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Section unifiée des agents -->
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-medium text-gray-900">
                        @if($currentMode === 'all_agents_manager' || $currentMode === 'all_agents_operator')
                            Accès actuel
                        @else
                            Agents avec accès ({{ $queuePermissions->count() }})
                        @endif
                    </h3>
                    
                    @if($currentMode === 'selected_agents')
                    <button type="button" onclick="openAddAgentsModal()" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-purple-600 border border-transparent rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Ajouter des agents
                    </button>
                    @endif
                </div>
                
                @if($currentMode === 'all_agents_manager')
                    <div class="p-4 border border-blue-200 rounded-lg bg-blue-50">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="font-medium text-blue-800">Gestion complète pour tous les agents</p>
                                <p class="text-sm text-blue-600">Tous les agents peuvent gérer la file d'attente et les tickets</p>
                            </div>
                        </div>
                    </div>
                @elseif($currentMode === 'all_agents_operator')
                    <div class="p-4 border border-green-200 rounded-lg bg-green-50">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <div>
                                <p class="font-medium text-green-800">Gestion des tickets pour tous les agents</p>
                                <p class="text-sm text-green-600">Tous les agents peuvent gérer les tickets seulement</p>
                            </div>
                        </div>
                    </div>
                @elseif($queuePermissions->count() > 0)
                    <div class="overflow-visible shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table id="agentsTable" class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Agent</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Permission</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Attribuée le</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($queuePermissions as $permission)
                                    @if($permission->user)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 w-10 h-10">
                                                    <div class="flex items-center justify-center w-10 h-10 bg-gray-300 rounded-full">
                                                        <span class="text-sm font-medium text-gray-700">
                                                            {{ strtoupper(substr($permission->user->name, 0, 2)) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $permission->user->name }}</div>
                                                    <div class="text-sm text-gray-500">{{ $permission->user->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="relative flex items-center space-x-2">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                    @if($permission->permission_type === 'manager') bg-green-100 text-green-800
                                                    @elseif($permission->permission_type === 'operator') bg-blue-100 text-blue-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif">
                                                    {{ $permission->permission_type === 'manager' ? 'Gestionnaire' : 'Opérateur' }}
                                                </span>
                                                {{-- <button onclick="togglePermissionDropdown({{ $permission->id }})" class="text-gray-400 hover:text-gray-600">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                                    </svg>
                                                </button> --}}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                            {{ $permission->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                            <div class="flex items-center space-x-2">
                                                <button onclick="togglePermissionDropdown({{ $permission->id }})" class="text-blue-600 transition-colors duration-200 hover:text-blue-900" title="Modifier la permission">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </button>
                                                <form method="POST" action="{{ route('admin.queues.permissions.destroy', [$queue, $permission]) }}" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 transition-colors duration-200 hover:text-red-900" title="Supprimer l'accès"
                                                            onclick="return confirm('Êtes-vous sûr de vouloir retirer l\'accès de cet agent ?')">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Dropdowns flottants pour les permissions -->
                    @foreach($queuePermissions as $permission)
                        @if($permission->user)
                        <div id="permission-dropdown-{{ $permission->id }}" class="fixed z-50 hidden bg-white border border-gray-200 rounded-md shadow-lg" style="min-width: 200px;">
                            <div class="py-1">
                                <form method="POST" action="{{ route('admin.queues.permissions.update', [$queue, $permission]) }}" class="block">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="permission_type" value="manager">
                                    <button type="submit" class="block w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-100 {{ $permission->permission_type === 'manager' ? 'bg-green-50 text-green-700' : '' }}">
                                        <span class="inline-flex items-center">
                                            <span class="w-2 h-2 mr-2 bg-green-500 rounded-full"></span>
                                            Gestionnaire
                                        </span>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.queues.permissions.update', [$queue, $permission]) }}" class="block">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="permission_type" value="operator">
                                    <button type="submit" class="block w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-100 {{ $permission->permission_type === 'operator' ? 'bg-blue-50 text-blue-700' : '' }}">
                                        <span class="inline-flex items-center">
                                            <span class="w-2 h-2 mr-2 bg-blue-500 rounded-full"></span>
                                            Opérateur
                                        </span>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endif
                    @endforeach
                @else
                    <div class="py-8 text-center">
                        <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun agent sélectionné</h3>
                        <p class="mt-1 text-sm text-gray-500">Commencez par ajouter des agents ci-dessus.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="mt-6 flex justify-between items-center">
            <a href="{{ route('admin.queues.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Retour aux files d'attente
            </a>
            
            <a href="{{ route('admin.queues.show', $queue) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                Voir la file
            </a>
        </div>
    </div>
</div>

@if(session('success'))
<div id="success-notification" class="fixed z-50 top-4 right-4">
    <div class="flex items-center px-6 py-4 text-white bg-green-500 rounded-lg shadow-lg">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
        </svg>
        {{ session('success') }}
    </div>
</div>
<script>
    setTimeout(function() {
        document.getElementById('success-notification').style.display = 'none';
    }, 3000);
</script>
@endif

@if(session('error'))
<div id="error-notification" class="fixed z-50 top-4 right-4">
    <div class="flex items-center px-6 py-4 text-white bg-red-500 rounded-lg shadow-lg">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
        </svg>
        {{ session('error') }}
    </div>
</div>
<script>
    setTimeout(function() {
        document.getElementById('error-notification').style.display = 'none';
    }, 3000);
</script>
@endif

<script>
// Fonctions pour la modal
function openAddAgentsModal() {
    document.getElementById('addAgentsModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeAddAgentsModal() {
    document.getElementById('addAgentsModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Fermer la modal en cliquant à l'extérieur
document.getElementById('addAgentsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeAddAgentsModal();
    }
});

// Recherche d'agents dans la modal
document.addEventListener('DOMContentLoaded', function() {
    const modalSearchInput = document.getElementById('modalAgentSearch');
    if (modalSearchInput) {
        modalSearchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const agentOptions = document.querySelectorAll('.agent-option');
            
            agentOptions.forEach(option => {
                const name = option.querySelector('.agent-name').textContent.toLowerCase();
                const email = option.querySelector('.agent-email').textContent.toLowerCase();
                
                if (name.includes(searchTerm) || email.includes(searchTerm)) {
                    option.style.display = 'flex';
                } else {
                    option.style.display = 'none';
                }
            });
        });
    }
});

// Sélection/désélection en masse
function selectAllAgents() {
    const checkboxes = document.querySelectorAll('input[name="agent_ids[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
}

function deselectAllAgents() {
    const checkboxes = document.querySelectorAll('input[name="agent_ids[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
}
</script>
    @endsection

    @push('scripts')
    <script>
        $(document).ready(function() {
            // Initialiser le tableau simple avec recherche
            initSimpleTable();
        });

        // Fonction pour initialiser un tableau simple avec recherche
        function initSimpleTable() {
            const table = document.getElementById('agentsTable');
            if (!table) return;
            
            // Ajouter une barre de recherche simple
            const searchDiv = document.createElement('div');
            searchDiv.className = 'mb-4 p-4 bg-gray-50 rounded-lg';
            searchDiv.innerHTML = `
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center mb-2 sm:mb-0">
                        <label class="mr-2 text-sm font-medium text-gray-700">Rechercher un agent:</label>
                        <input type="text" id="simpleSearch" placeholder="Nom ou email..." 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:w-64">
                    </div>
                    <div class="text-sm text-gray-500">
                        <span id="resultCount">0</span> agent(s) trouvé(s)
                    </div>
                </div>
            `;
            
            table.parentNode.insertBefore(searchDiv, table);
            
            // Fonction de recherche
            document.getElementById('simpleSearch').addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                const rows = table.querySelectorAll('tbody tr');
                let visibleCount = 0;
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    const isVisible = text.includes(searchTerm);
                    row.style.display = isVisible ? '' : 'none';
                    if (isVisible) visibleCount++;
                });
                
                // Mettre à jour le compteur
                document.getElementById('resultCount').textContent = visibleCount;
            });
            
            // Initialiser le compteur
            document.getElementById('resultCount').textContent = table.querySelectorAll('tbody tr').length;
        }

        // Fonction pour gérer les dropdowns de permissions
        function togglePermissionDropdown(permissionId) {
            const dropdown = document.getElementById(`permission-dropdown-${permissionId}`);
            const allDropdowns = document.querySelectorAll('[id^="permission-dropdown-"]');
            
            // Fermer tous les autres dropdowns
            allDropdowns.forEach(d => {
                if (d.id !== `permission-dropdown-${permissionId}`) {
                    d.classList.add('hidden');
                }
            });
            
            // Toggle le dropdown actuel
            const isHidden = dropdown.classList.contains('hidden');
            
            if (isHidden) {
                // Trouver le bouton qui a été cliqué
                const clickedButton = event.target.closest('button[onclick*="togglePermissionDropdown"]');
                if (clickedButton) {
                    const buttonRect = clickedButton.getBoundingClientRect();
                    
                    // Positionner le dropdown
                    dropdown.style.top = `${buttonRect.bottom + 5}px`;
                    dropdown.style.left = `${buttonRect.left}px`;
                    
                    // Vérifier si le dropdown dépasse à droite
                    const dropdownRect = dropdown.getBoundingClientRect();
                    if (dropdownRect.right > window.innerWidth) {
                        dropdown.style.left = `${window.innerWidth - dropdownRect.width - 10}px`;
                    }
                    
                    // Vérifier si le dropdown dépasse en bas
                    if (dropdownRect.bottom > window.innerHeight) {
                        dropdown.style.top = `${buttonRect.top - dropdownRect.height - 5}px`;
                    }
                }
                
                dropdown.classList.remove('hidden');
            } else {
                dropdown.classList.add('hidden');
            }
        }

        // Fermer les dropdowns quand on clique ailleurs
        document.addEventListener('click', function(event) {
            if (!event.target.closest('[id^="permission-dropdown-"]') && !event.target.closest('button[onclick*="togglePermissionDropdown"]')) {
                const allDropdowns = document.querySelectorAll('[id^="permission-dropdown-"]');
                allDropdowns.forEach(dropdown => {
                    dropdown.classList.add('hidden');
                });
            }
        });

        // Fermer les dropdowns avec la touche Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const allDropdowns = document.querySelectorAll('[id^="permission-dropdown-"]');
                allDropdowns.forEach(dropdown => {
                    dropdown.classList.add('hidden');
                });
            }
        });
    </script>
    @endpush
