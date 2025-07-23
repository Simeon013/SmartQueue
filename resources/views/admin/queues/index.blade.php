@extends('layouts.admin')

@section('header', 'Gestion des files d\'attente')

@section('content')
<div class="bg-white rounded-lg shadow" x-data="{}">
    <div class="p-6 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">Liste des files d'attente</h3>
            <a href="{{ route('admin.queues.create') }}" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase bg-blue-600 rounded-md border border-transparent transition duration-150 ease-in-out hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <svg class="mr-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nouvelle file
            </a>
        </div>

        <!-- Formulaire de filtrage -->
        <div id="filter-container" class="space-y-4 mb-6">
            <form id="filter-form" method="GET" action="{{ route('admin.queues.index') }}" class="space-y-4">
                <div class="flex flex-col space-y-4 md:flex-row md:space-y-0 md:space-x-4">
                    <!-- Champ de recherche par nom ou créateur -->
                    <div class="flex-1">
                        <label for="name" class="block text-sm font-medium text-gray-700">Rechercher</label>
                        <div class="relative mt-1">
                            <input type="text" name="name" id="name" value="{{ request('name') }}" 
                                   class="search-input block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm pr-10"
                                   placeholder="Nom de la file ou du créateur...">
                            @if(request('name'))
                                <button type="button" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600" id="clear-search">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Filtre par statut -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Statut</label>
                        <select id="status" name="status" class="filter-select mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm">
                            <option value="">Tous les statuts</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Actif</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactif</option>
                        </select>
                    </div>

                    <!-- Filtre par permission (uniquement pour les agents) -->
                    @if(auth()->user()->isAgent())
                    <div>
                        <label for="permission" class="block text-sm font-medium text-gray-700">Type d'accès</label>
                        <select id="permission" name="permission" class="filter-select mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm">
                            <option value="">Tous les accès</option>
                            <option value="manage" {{ request('permission') === 'manage' ? 'selected' : '' }}>Gestion</option>
                            <option value="view" {{ request('permission') === 'view' ? 'selected' : '' }}>Consultation</option>
                        </select>
                    </div>
                    @endif

                    <div class="flex items-end space-x-2">
                        <a href="{{ route('admin.queues.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                            Réinitialiser
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                            </svg>
                            Filtrer
                        </button>
                    </div>
                </div>
            </form>
            <div id="loading-indicator" class="hidden text-center py-2">
                <div class="inline-flex items-center text-blue-600">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Chargement...</span>
                </div>
                <!-- Champ caché pour le tri -->
                <input type="hidden" name="sort" value="{{ request('sort', 'created_at') }}">
                <input type="hidden" name="direction" value="{{ request('direction', 'desc') }}">
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('sort') === 'name' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="inline-flex items-center group">
                            Nom
                            @if(request('sort') === 'name')
                                @if(request('direction') === 'asc')
                                    <svg class="ml-2 w-4 h-4 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    </svg>
                                @else
                                    <svg class="ml-2 w-4 h-4 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                @endif
                            @else
                                <svg class="ml-2 w-4 h-4 text-gray-300 group-hover:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                </svg>
                            @endif
                        </a>
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'is_active', 'direction' => request('sort') === 'is_active' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="inline-flex items-center group">
                            Statut
                            @if(request('sort') === 'is_active')
                                @if(request('direction') === 'asc')
                                    <svg class="ml-2 w-4 h-4 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    </svg>
                                @else
                                    <svg class="ml-2 w-4 h-4 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                @endif
                            @else
                                <svg class="ml-2 w-4 h-4 text-gray-300 group-hover:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                </svg>
                            @endif
                        </a>
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'pending_tickets', 'direction' => request('sort') === 'pending_tickets' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="inline-flex items-center group" data-sort="pending_tickets">
                            Tickets en attente
                            @if(request('sort') === 'pending_tickets')
                                @if(request('direction') === 'asc')
                                    <svg class="ml-2 w-4 h-4 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    </svg>
                                @else
                                    <svg class="ml-2 w-4 h-4 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                @endif
                            @else
                                <svg class="ml-2 w-4 h-4 text-gray-300 group-hover:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                </svg>
                            @endif
                        </a>
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('sort') === 'created_at' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="inline-flex items-center group" data-sort="created_at">
                            Date de création
                            @if(request('sort') === 'created_at')
                                @if(request('direction') === 'asc')
                                    <svg class="ml-2 w-4 h-4 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    </svg>
                                @else
                                    <svg class="ml-2 w-4 h-4 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                @endif
                            @else
                                <svg class="ml-2 w-4 h-4 text-gray-300 group-hover:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                </svg>
                            @endif
                        </a>
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'creator_name', 'direction' => request('sort') === 'creator_name' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="inline-flex items-center group" data-sort="creator_name">
                            Créé par
                            @if(request('sort') === 'creator_name')
                                @if(request('direction') === 'asc')
                                    <svg class="ml-2 w-4 h-4 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    </svg>
                                @else
                                    <svg class="ml-2 w-4 h-4 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                @endif
                            @else
                                <svg class="ml-2 w-4 h-4 text-gray-300 group-hover:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                </svg>
                            @endif
                        </a>
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($queues as $queue)
                    <tr class="relative cursor-pointer hover:bg-blue-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $queue->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $queue->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $queue->is_active ? 'Ouverte' : 'Fermée' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                            {{ $queue->tickets()->where('status', 'waiting')->count() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $queue->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($queue->creator)
                                {{ $queue->creator->name }}
                            @else
                                <span class="text-gray-400">Inconnu</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                            <div class="flex items-center justify-end space-x-2">
                                <!-- Bouton Voir -->
                                <a href="{{ route('admin.queues.show', $queue) }}" 
                                   class="inline-flex items-center p-1.5 border border-transparent rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                   title="Voir les détails">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>

                                @php
                                    $user = auth()->user();
                                    $canManage = $user->hasRole('super-admin') ||
                                                $user->hasRole('admin') ||
                                                $queue->userCanManage($user);
                                    $canDelete = $user->hasRole('super-admin') ||
                                                $user->hasRole('admin') ||
                                                $queue->userOwns($user);
                                @endphp

                                @if($canManage)
                                    <!-- Bouton Modifier -->
                                    <a href="{{ route('admin.queues.edit', $queue) }}" 
                                       class="inline-flex items-center p-1.5 border border-transparent rounded-md text-yellow-700 bg-yellow-100 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                                       title="Modifier la file">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>

                                    <!-- Bouton Gérer les permissions -->
                                    <a href="{{ route('admin.queues.permissions', $queue) }}" 
                                       class="inline-flex items-center p-1.5 border border-transparent rounded-md text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                       title="Gérer les permissions">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </a>
                                @endif

                                @if($canDelete)
                                    <!-- Bouton Supprimer -->
                                    <form action="{{ route('admin.queues.destroy', $queue) }}" method="POST" class="inline" x-data="{ confirmDelete: false }">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                                @click="if(confirm('Êtes-vous sûr de vouloir supprimer cette file ?')) { $el.closest('form').submit(); }"
                                                class="inline-flex items-center p-1.5 border border-transparent rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                title="Supprimer la file">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            Aucune file d'attente trouvée
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($queues->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $queues->links() }}
        </div>
    @endif
</div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mise à jour des icônes de tri au chargement
        updateSortIcons();
        
        // Éléments du DOM
        const filterForm = document.getElementById('filter-form');
        const searchInput = document.querySelector('.search-input');
        const filterSelects = document.querySelectorAll('.filter-select');
        const loadingIndicator = document.getElementById('loading-indicator');
        const queuesTable = document.querySelector('table tbody');
        let typingTimer;
        const doneTypingInterval = 500; // ms
        
        // Fonction pour mettre à jour les icônes de tri
        function updateSortIcons() {
            const currentSort = '{{ request('sort') }}';
            const currentDirection = '{{ request('direction') }}';
            
            // Réinitialiser toutes les icônes
            document.querySelectorAll('.sort-icon').forEach(icon => {
                icon.innerHTML = `
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                    </svg>
                `;
            });
            
            // Mettre à jour l'icône de la colonne triée
            if (currentSort && currentDirection) {
                const activeIcon = document.querySelector(`[data-sort='${currentSort}'] .sort-icon`);
                if (activeIcon) {
                    activeIcon.innerHTML = currentDirection === 'asc' ? `
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        </svg>
                    ` : `
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    `;
                }
            }
        }
        
        // Fonction pour charger les données via AJAX
        function loadQueues(query = '') {
            // Afficher l'indicateur de chargement
            loadingIndicator.classList.remove('hidden');
            
            // Récupérer les valeurs du formulaire
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData);
            
            // Ajouter le tri actuel s'il existe
            const currentSort = '{{ request('sort') }}';
            const currentDirection = '{{ request('direction') }}';
            if (currentSort) {
                params.set('sort', currentSort);
                params.set('direction', currentDirection);
            }
            
            // Effectuer la requête AJAX
            fetch(`{{ route('admin.queues.index') }}?${params.toString()}`)
                .then(response => response.text())
                .then(html => {
                    // Extraire le contenu de la table depuis la réponse
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newTableBody = doc.querySelector('table tbody');
                    const newPagination = doc.querySelector('.pagination');
                    
                    // Mettre à jour le contenu du tableau
                    if (newTableBody) {
                        queuesTable.innerHTML = newTableBody.innerHTML;
                    }
                    
                    // Mettre à jour la pagination si elle existe
                    if (newPagination) {
                        const existingPagination = document.querySelector('.pagination');
                        if (existingPagination) {
                            existingPagination.outerHTML = newPagination.outerHTML;
                        } else {
                            queuesTable.insertAdjacentElement('afterend', newPagination);
                        }
                    }
                    
                    // Mettre à jour l'URL sans recharger la page
                    const newUrl = `${window.location.pathname}?${params.toString()}`;
                    window.history.pushState({ path: newUrl }, '', newUrl);
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des données:', error);
                })
                .finally(() => {
                    // Masquer l'indicateur de chargement
                    loadingIndicator.classList.add('hidden');
                });
        }
        
        // Gestionnaire d'événement pour la recherche en temps réel
        searchInput.addEventListener('input', function() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
                loadQueues(this.value);
                // Afficher/masquer le bouton de réinitialisation
                const clearButton = document.getElementById('clear-search');
                if (this.value.trim() !== '') {
                    clearButton.classList.remove('hidden');
                } else {
                    clearButton.classList.add('hidden');
                }
            }, doneTypingInterval);
        });
        
        // Gestionnaire d'événement pour le bouton de réinitialisation de la recherche
        document.getElementById('clear-search')?.addEventListener('click', function() {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
            this.classList.add('hidden');
        });
        
        // Gestionnaire d'événement pour les sélecteurs de filtre
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                loadQueues();
            });
        });
        
        // Gestionnaire d'événement pour le tri des colonnes
        document.querySelectorAll('th[data-sort]').forEach(header => {
            header.addEventListener('click', function() {
                const sortField = this.getAttribute('data-sort');
                const currentSort = '{{ request('sort') }}';
                const currentDirection = '{{ request('direction') }}';
                
                let newDirection = 'asc';
                if (currentSort === sortField) {
                    newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
                }
                
                // Mettre à jour les paramètres de tri
                const url = new URL(window.location);
                url.searchParams.set('sort', sortField);
                url.searchParams.set('direction', newDirection);
                
                // Charger les données avec le nouveau tri
                window.location.href = url.toString();
            });
        });
        
        // Gestionnaire d'événement pour le bouton de réinitialisation
        const resetButton = document.querySelector('button[type="reset"]');
        if (resetButton) {
            resetButton.addEventListener('click', function() {
                // Réinitialiser le formulaire
                filterForm.reset();
                // Recharger les données
                loadQueues();
            });
        }
    });
</script>
@endpush

@endsection
