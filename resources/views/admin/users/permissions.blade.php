@extends('layouts.admin')

@section('header', 'Gestion des permissions')

@push('styles')
<style>
    /* Badges personnalisés */
    .badge {
        @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
    }
    .badge-manager {
        @apply bg-green-100 text-green-800;
    }
    .badge-operator {
        @apply bg-blue-100 text-blue-800;
    }
    .badge-global {
        @apply bg-purple-100 text-purple-800;
    }

    /* Style personnalisé pour DataTables */
    .dataTables_wrapper {
        @apply w-full;
    }
    .dataTables_wrapper .dataTables_length select {
        @apply mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md;
    }
    .dataTables_wrapper .dataTables_filter input {
        @apply shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        @apply relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        @apply z-10 bg-indigo-50 border-indigo-500 text-indigo-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium;
    }
    .dataTables_wrapper .dataTables_info {
        @apply text-sm text-gray-700;
    }
    .dataTables_wrapper table.dataTable {
        @apply min-w-full divide-y divide-gray-200;
    }
    .dataTables_wrapper table.dataTable thead th {
        @apply px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider;
    }
    .dataTables_wrapper table.dataTable tbody td {
        @apply px-6 py-4 whitespace-nowrap text-sm text-gray-500;
    }
    .dataTables_wrapper table.dataTable tbody tr:hover {
        @apply bg-gray-50;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Informations de l'utilisateur -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Permissions de {{ $user->name }}</h3>
            <p class="text-sm text-gray-500 mt-1">{{ $user->email }}</p>
        </div>
    </div>

    <!-- Permissions via le rôle -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Permissions via le rôle</h3>
            <p class="text-sm text-gray-500 mt-1">Ces permissions sont héritées du rôle attribué à l'utilisateur</p>
        </div>
        <div class="p-6">
            @if($user->role)
                <div class="space-y-4">
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-medium text-gray-900">{{ $user->role->label() }}</h4>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ count($rolePermissions) }} permissions
                            </span>
                        </div>
                        @if(count($rolePermissions) > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                @foreach($rolePermissions as $permission)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-green-100 text-green-800">
                                        {{ $permission }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500">Aucune permission</p>
                        @endif
                    </div>
                </div>
            @else
                <p class="text-gray-500 text-center py-4">Aucun rôle attribué</p>
            @endif
        </div>
    </div>

    <!-- Permissions sur les files d'attente en attente -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Files en attente</h3>
                <div class="flex space-x-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ $activeQueuePermissions->count() }} file(s) en attente
                    </span>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-1">Liste des files d'attente actives sur lesquelles l'utilisateur a des permissions</p>
        </div>
        <div class="p-6">
            @if($activeQueuePermissions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    File d'attente
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Service
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Permission
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Attribuée le
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($activeQueuePermissions as $permission)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $permission->queue->name }}
                                                    @if($permission->is_global ?? false)
                                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                                            Globale
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $permission->queue->code }}
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $permission->queue->status->value === 'open' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                       {{ $permission->queue->status }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $badgeClasses = [
                                                'manager' => 'bg-green-100 text-green-800',
                                                'operator' => 'bg-blue-100 text-blue-800',
                                                'default' => 'bg-gray-100 text-gray-800'
                                            ];
                                            $badgeClass = $badgeClasses[$permission->permission_type] ?? $badgeClasses['default'];
                                        @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badgeClass }}">
                                        {{ $permission->permission_type === 'manager' ? 'Gestion complète' : 'Gestion des tickets' }}
                                    </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $permission->created_at->format('d/m/Y H:i') }}
                                        @if($permission->grantedBy)
                                        <div class="text-xs text-gray-400">
                                            Par {{ $permission->grantedBy->name }}
                                        </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if(!($permission->is_global ?? false))
                                        <form method="POST" action="{{ route('admin.queue-permissions.destroy', $permission) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900"
                                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette permission ?')">
                                                Supprimer
                                            </button>
                                        </form>
                                        @else
                                        <span class="text-gray-400">Permission globale</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-4">Aucune permission sur les files en attente</p>
            @endif
        </div>
    </div>

    <!-- Bouton pour afficher toutes les files -->
    <div class="mb-6">
        <button id="showAllQueuesBtn" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
            </svg>
            @if(isset($inactiveQueuePermissions) && $inactiveQueuePermissions->count() > 0)
                Afficher toutes les files ({{ $inactiveQueuePermissions->count() }} inactives)
            @else
                Afficher toutes les files
            @endif
        </button>
    </div>

    <!-- Tableau pour toutes les files (caché par défaut) -->
    <div id="allQueuesTable" class="bg-white rounded-lg shadow overflow-hidden" style="display: none;">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Toutes les files d'attente</h3>
            <p class="text-sm text-gray-500 mt-1">Liste complète de toutes les files d'attente avec les permissions de l'utilisateur</p>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto bg-white rounded-lg shadow">
                <table id="queuesTable" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File d'attente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type de permission</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attribuée le</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Les données seront chargées dynamiquement par DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Formulaire d'ajout de permission -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Ajouter une permission</h3>
            <p class="text-sm text-gray-500 mt-1">Attribuer une nouvelle permission sur une file d'attente</p>
        </div>
        <div class="p-6">
            <form action="{{ route('admin.queue-permissions.store') }}" method="POST">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Sélection de la file d'attente -->
                    <div>
                        <label for="queue_id" class="block text-sm font-medium text-gray-700 mb-1">File d'attente</label>
                        <select id="queue_id" name="queue_id" required
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">Sélectionner une file d'attente</option>
                            @foreach($allQueues as $queue)
                                @php
                                    $hasPermission = $user->queuePermissions->contains('queue_id', $queue->id) ||
                                                   $globalQueueIds->contains($queue->id);
                                @endphp
                                <option value="{{ $queue->id }}" {{ $hasPermission ? 'disabled' : '' }}>
                                    {{ $queue->name }} ({{ $queue->code }})
                                    @if($queue->service)
                                        - {{ $queue->service->name }}
                                    @endif
                                    @if($hasPermission)
                                        (Déjà autorisé)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Type de permission -->
                    <div>
                        <label for="permission_type" class="block text-sm font-medium text-gray-700 mb-1">Type de permission</label>
                        <select id="permission_type" name="permission_type" required
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">Sélectionner un type</option>
                            <option value="manager">Gestion complète</option>
                            <option value="operator">Gestion des tickets uniquement</option>
                        </select>
                    </div>
                </div>

                <!-- Bouton de soumission -->
                <div class="mt-6">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Ajouter la permission
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex justify-between items-center">
        <a href="{{ route('admin.users.show', $user) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Retour aux détails
        </a>
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Retour à la liste
        </a>
    </div>
</div>
@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        // Afficher/masquer toutes les files
        $('#showAllQueuesBtn').click(function() {
            $('#allQueuesTable').slideToggle();

            // Initialiser DataTable uniquement si c'est la première fois
            if (!$.fn.DataTable.isDataTable('#queuesTable')) {
                initializeDataTable();
            }
        });

        function initializeDataTable() {
            // Vérifier si les données sont disponibles
            var tableData = @json($queuesDataTable ?? []);

            if (tableData.length === 0) {
                $('#allQueuesTable').html('<div class="p-6"><p class="text-gray-500 text-center">Aucune donnée disponible</p></div>');
                return;
            }

            // Initialiser DataTable avec un style personnalisé
            var table = $('#queuesTable').DataTable({
                processing: true,
                serverSide: false,
                data: tableData,
                autoWidth: false,
                columns: [
                    {
                        data: 'queue_name',
                        className: 'px-6 py-4 whitespace-nowrap',
                        render: function(data, type, row) {
                            let html = '<div class="text-sm font-medium text-gray-900">' + (data || 'N/A') + '</div>';
                            if (row.is_global) {
                                html += '<span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Globale</span>';
                            }
                            return html;
                        }
                    },
                    {
                        data: 'queue_code',
                        className: 'px-6 py-4 whitespace-nowrap',
                        defaultContent: 'N/A',
                        render: function(data) {
                            return '<div class="text-sm text-gray-500">' + (data || 'N/A') + '</div>';
                        }
                    },
                    {
                        data: 'service_name',
                        className: 'px-6 py-4 whitespace-nowrap',
                        defaultContent: 'N/A',
                        render: function(data) {
                            return '<div class="text-sm text-gray-500">' + (data || 'N/A') + '</div>';
                        }
                    },
                    {
                        data: 'permission_type',
                        className: 'px-6 py-4 whitespace-nowrap',
                        render: function(data, type, row) {
                            if (!data) return '<div class="text-sm text-gray-500">N/A</div>';
                            const badgeClass = data === 'manager' ? 'badge-manager' : 'badge-operator';
                            const label = data === 'manager' ? 'Gestion complète' : 'Gestion des tickets';
                            return '<span class="badge ' + badgeClass + '">' + label + '</span>';
                        }
                    },
                    {
                        data: 'status',
                        className: 'px-6 py-4 whitespace-nowrap',
                        render: function(data) {
                            if (!data) return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Inconnu</span>';

                            const statusMap = {
                                'open': { class: 'bg-green-100 text-green-800', label: 'Active' },
                                'waiting': { class: 'bg-yellow-100 text-yellow-800', label: 'En attente' },
                                'paused': { class: 'bg-blue-100 text-blue-800', label: 'En pause' },
                                'completed': { class: 'bg-gray-100 text-gray-800', label: 'Terminée' },
                                'cancelled': { class: 'bg-red-100 text-red-800', label: 'Annulée' }
                            };

                            const status = statusMap[data] || { class: 'bg-gray-100 text-gray-800', label: data };
                            return `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${status.class}">${status.label}</span>`;
                        }
                    },
                    {
                        data: 'created_at',
                        className: 'px-6 py-4 whitespace-nowrap',
                        defaultContent: 'N/A',
                        render: function(data) {
                            return '<div class="text-sm text-gray-500">' + (data || 'N/A') + '</div>';
                        }
                    },
                    {
                        data: 'id',
                        className: 'px-6 py-4 whitespace-nowrap text-sm font-medium',
                        orderable: false,
                        render: function(data, type, row) {
                            if (row.is_global) {
                                return '<span class="text-gray-400">Permission globale</span>';
                            } else if (data && row.can_delete) {
                                return `
                                    <form method="POST" action="/admin/queue-permissions/${data}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900"
                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette permission ?')">
                                            Supprimer
                                        </button>
                                    </form>
                                `;
                            } else if (data) {
                                return '<span class="text-gray-400">Action non autorisée</span>';
                            }
                            return 'N/A';
                        }
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json',
                    emptyTable: 'Aucune donnée disponible dans le tableau',
                    info: 'Affichage de _START_ à _END_ sur _TOTAL_ entrées',
                    infoEmpty: 'Affichage de 0 à 0 sur 0 entrées',
                    infoFiltered: '(filtré à partir de _MAX_ entrées au total)',
                    lengthMenu: 'Afficher _MENU_ entrées',
                    loadingRecords: 'Chargement...',
                    processing: 'Traitement...',
                    search: 'Rechercher :',
                    searchPlaceholder: 'Rechercher...',
                    zeroRecords: 'Aucun enregistrement correspondant trouvé',
                    paginate: {
                        first: 'Premier',
                        last: 'Dernier',
                        next: 'Suivant',
                        previous: 'Précédent'
                    },
                    aria: {
                        sortAscending: ': activer pour trier la colonne par ordre croissant',
                        sortDescending: ': activer pour trier la colonne par ordre décroissant'
                    }
                },
                order: [[5, 'desc']], // Trier par date d'attribution par défaut
                pageLength: 10,
                responsive: true,
                dom: `
                    <"flex flex-col md:flex-row items-center justify-between pb-4 space-y-4 md:space-y-0"
                        <"flex items-center space-x-4"
                            l
                            f
                        >
                        <"mt-4 md:mt-0"p>
                    >
                    rt
                    <"flex flex-col md:flex-row items-center justify-between pt-4 space-y-4 md:space-y-0"
                        <"text-sm text-gray-600"i>
                        <"mt-4 md:mt-0"p>
                    >
                `,
                initComplete: function() {
                    // Personnalisation des champs de recherche et de sélection
                    $('.dataTables_filter input')
                        .addClass('mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md')
                        .attr('placeholder', 'Rechercher...');

                    $('.dataTables_length select')
                        .addClass('mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md');

                    // Ajouter des classes aux boutons de pagination
                    $('.dataTables_paginate .paginate_button')
                        .removeClass('paginate_button')
                        .addClass('px-3 py-1 rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50');

                    $('.dataTables_paginate .current')
                        .removeClass('paginate_button current')
                        .addClass('px-3 py-1 rounded-md border border-indigo-500 bg-indigo-50 text-sm font-medium text-indigo-600');
                }
            });
        }
    });
</script>
@endpush

@endsection
