@extends('layouts.admin')

@section('title', 'Historique des Files d\'Attente')

@push('styles')
<style>
    .stat-card {
        @apply bg-white overflow-hidden shadow rounded-lg transition-all duration-200 hover:shadow-md;
        border-top: 4px solid transparent;
    }
    .stat-card:hover {
        transform: translateY(-2px);
    }
    .stat-card:nth-child(1) { border-top-color: #3B82F6; } /* Blue */
    .stat-card:nth-child(2) { border-top-color: #10B981; } /* Green */
    .stat-card:nth-child(3) { border-top-color: #F59E0B; } /* Yellow */
    .stat-card:nth-child(4) { border-top-color: #8B5CF6; } /* Purple */

    .stat-value {
        @apply text-2xl font-semibold text-gray-900;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }
    .stat-label {
        @apply text-xs font-medium text-gray-500 uppercase tracking-wider;
    }
    .progress-bar {
        @apply w-full bg-gray-200 rounded-full h-2 overflow-hidden mt-2;
    }
    .progress-bar-fill {
        @apply h-full rounded-full transition-all duration-500 ease-out;
    }
    .filter-section {
        @apply bg-white p-4 rounded-lg shadow mb-6 border border-gray-200;
    }
    .filter-title {
        @apply text-lg font-medium text-gray-900 mb-4 flex items-center;
    }
    .filter-button {
        @apply inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500;
    }
    .reset-button {
        @apply ml-3 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500;
    }

    /* Animation pour les cartes */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .stat-card {
        animation: fadeInUp 0.5s ease-out forwards;
    }
    .stat-card:nth-child(1) { animation-delay: 0.1s; }
    .stat-card:nth-child(2) { animation-delay: 0.2s; }
    .stat-card:nth-child(3) { animation-delay: 0.3s; }
    .stat-card:nth-child(4) { animation-delay: 0.4s; }
</style>
@endpush

@section('content')
    <div class="p-6 mb-2 bg-white rounded-lg shadow">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Historique des Files d'Attente</h1>
                <p class="mt-1 text-sm text-gray-500">Analyse et suivi des performances des files d'attente</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <!-- Carte Files d'attente -->
            <div class="p-4 bg-white rounded-lg border border-gray-200">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-500">Total Files</div>
                        <div class="text-xl font-bold">{{ $stats['total_queues'] }}</div>
                        <div class="text-xs text-green-600">{{ $stats['active_queues'] }} actives</div>
                    </div>
                </div>
            </div>

            <!-- Carte Tickets -->
            <div class="p-4 bg-white rounded-lg border border-gray-200">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-500">Tickets Créés</div>
                        <div class="text-xl font-bold">{{ $stats['total_tickets'] }}</div>
                        <div class="text-xs text-blue-600">{{ $stats['completed_tickets'] }} terminés</div>
                    </div>
                </div>
            </div>

            <!-- Carte Aujourd'hui -->
            <div class="p-4 bg-white rounded-lg border border-gray-200">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-500">Aujourd'hui</div>
                        <div class="text-xl font-bold">{{ $stats['today_tickets'] ?? 0 }} tickets</div>
                        <div class="text-xs text-purple-600">{{ $stats['today_queues'] ?? 0 }} files</div>
                    </div>
                </div>
            </div>

            <!-- Carte Temps d'attente -->
            <div class="p-4 bg-white rounded-lg border border-gray-200">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-500">Temps moyen</div>
                        <div class="text-xl font-bold">{{ round($stats['avg_wait_time'] ?? 0, 1) }} min</div>
                        <div class="text-xs {{ ($stats['avg_wait_time'] ?? 0) < 5 ? 'text-green-600' : 'text-red-600' }}">
                            {{ ($stats['avg_wait_time'] ?? 0) < 5 ? 'Rapide' : 'À surveiller' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Scripts JavaScript spécifiques à la page d'historique
        document.addEventListener('DOMContentLoaded', function() {
            // Initialisation des tooltips si nécessaire
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Gestion des filtres avancés
            const advancedFiltersBtn = document.getElementById('toggle-advanced-filters');
            if (advancedFiltersBtn) {
                advancedFiltersBtn.addEventListener('click', function() {
                    const filters = document.getElementById('advanced-filters');
                    if (filters) {
                        filters.classList.toggle('hidden');
                    }
                });
            }

            // Mise à jour en temps réel des statistiques
            function updateStats() {
                fetch('{{ route("admin.history.stats") }}')
                    .then(response => response.json())
                    .then(data => {
                        // Mettre à jour les éléments de la page avec les nouvelles données
                        document.querySelector('[data-stat="total-queues"]').textContent = data.total_queues;
                        document.querySelector('[data-stat="active-queues"]').textContent = data.active_queues;
                        document.querySelector('[data-stat="total-tickets"]').textContent = data.total_tickets;
                        document.querySelector('[data-stat="completed-tickets"]').textContent = data.completed_tickets;
                        document.querySelector('[data-stat="avg-wait-time"]').textContent = data.avg_wait_time.toFixed(1);
                    });
            }

            // Mettre à jour les stats toutes les 60 secondes
            setInterval(updateStats, 60000);
        });
    </script>
    @endpush

    <!-- Filtres principaux -->
    <div class="p-4 mb-2 bg-white rounded-lg shadow">
        {{-- <h2 class="mb-4 text-lg font-medium text-gray-900">Filtres de recherche</h2> --}}
        <form action="{{ route('admin.history.index') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="md:col-span-2">
                    <label for="search" class="block mb-1 text-sm font-medium text-gray-700">Recherche</label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" name="search" id="search" value="{{ $search }}" class="block py-2 pr-3 pl-10 w-full leading-5 placeholder-gray-500 bg-white rounded-md border border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Rechercher...">
                    </div>
                </div>

                <div>
                    <label for="status" class="block mb-1 text-sm font-medium text-gray-700">Statut</label>
                    <select id="status" name="status" class="block py-2 pr-10 pl-3 w-full text-base rounded-md border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="all" {{ $status === 'all' || $status === null ? 'selected' : '' }}>Tous les statuts</option>
                        @foreach(\App\Enums\QueueStatus::cases() as $statusOption)
                            <option value="{{ $statusOption->value }}" {{ $status === $statusOption->value ? 'selected' : '' }}>
                                {{ $statusOption->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="service_id" class="block mb-1 text-sm font-medium text-gray-700">Service</label>
                    <select id="service_id" name="service_id" class="block py-2 pr-10 pl-3 w-full text-base rounded-md border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">Tous les services</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" {{ $serviceId == $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex justify-end pt-2 space-x-3">
                <a href="{{ route('admin.history.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-md border border-gray-300 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Réinitialiser
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md border border-transparent shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="mr-2 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Appliquer les filtres
                </button>
            </div>
        </form>
    </div>

    <!-- Tableau d'historique -->
    <div class="overflow-hidden bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900">
                Détails des Files d'Attente
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                Liste complète des files d'attente avec statistiques détaillées
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                            <div class="flex items-center">
                                <span>File d'Attente</span>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="ml-1">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                    </svg>
                                </a>
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                            <div class="flex items-center">
                                <span>Statut</span>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="ml-1">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                    </svg>
                                </a>
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                            <div class="flex items-center">
                                <span>Activité</span>
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                            <div class="flex items-center">
                                <span>Création</span>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="ml-1">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                    </svg>
                                </a>
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($queues as $queue)
                        @php
                            $waitingTickets = $queue->tickets->where('status', 'waiting')->count();
                            $servedTickets = $queue->tickets->where('status', 'served')->count();
                            $cancelledTickets = $queue->tickets->where('status', 'cancelled')->count();
                            $totalTickets = $queue->tickets->count();
                            $completionRate = $totalTickets > 0 ? round(($servedTickets / $totalTickets) * 100) : 0;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex flex-shrink-0 justify-center items-center w-10 h-10 bg-blue-100 rounded-full">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $queue->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $queue->establishment->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusClass = [
                                        'open' => 'bg-green-100 text-green-800',
                                        'paused' => 'bg-yellow-100 text-yellow-800',
                                        'blocked' => 'bg-red-100 text-red-800',
                                        'closed' => 'bg-gray-100 text-gray-800',
                                    ][$queue->status->value] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full {{ $statusClass }}">
                                    {{ $queue->status->label() }}
                                </span>
                                <div class="mt-1 text-xs text-gray-500">
                                    {{ $totalTickets }} tickets au total
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    <div class="flex items-center">
                                        <div class="mr-2 w-16 text-xs text-right text-gray-500">En attente:</div>
                                        <div class="flex-1">
                                            <div class="flex items-center">
                                                <div class="mr-2 w-12 text-right">{{ $waitingTickets }}</div>
                                                <div class="flex-1 h-2 bg-gray-200 rounded-full">
                                                    <div class="h-2 bg-yellow-500 rounded-full" style="width: {{ $totalTickets > 0 ? ($waitingTickets / $totalTickets) * 100 : 0 }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center mt-1">
                                        <div class="mr-2 w-16 text-xs text-right text-gray-500">Terminés:</div>
                                        <div class="flex-1">
                                            <div class="flex items-center">
                                                <div class="mr-2 w-12 text-right">{{ $servedTickets }}</div>
                                                <div class="flex-1 h-2 bg-gray-200 rounded-full">
                                                    <div class="h-2 bg-green-500 rounded-full" style="width: {{ $totalTickets > 0 ? ($servedTickets / $totalTickets) * 100 : 0 }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center mt-1">
                                        <div class="mr-2 w-16 text-xs text-right text-gray-500">Annulés:</div>
                                        <div class="flex-1">
                                            <div class="flex items-center">
                                                <div class="mr-2 w-12 text-right">{{ $cancelledTickets }}</div>
                                                <div class="flex-1 h-2 bg-gray-200 rounded-full">
                                                    <div class="h-2 bg-red-500 rounded-full" style="width: {{ $totalTickets > 0 ? ($cancelledTickets / $totalTickets) * 100 : 0 }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $queue->created_at->format('d/m/Y') }}</div>
                                <div class="text-sm text-gray-500">{{ $queue->created_at->format('H:i') }}</div>
                                @if($queue->end_time)
                                    <div class="mt-1 text-xs text-gray-500">
                                        Durée: {{ $queue->created_at->diffInHours($queue->end_time) }}h
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                <div class="flex justify-end space-x-2">
                                    <a href="{{ route('admin.queues.show', $queue) }}" class="flex items-center text-blue-600 hover:text-blue-900 group" title="Voir les détails">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="#" class="flex items-center text-indigo-600 hover:text-indigo-900 group" title="Télécharger le rapport">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-sm text-center text-gray-500">
                                <svg class="mx-auto w-12 h-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune file d'attente trouvée</h3>
                                <p class="mt-1 text-sm text-gray-500">Aucune file d'attente ne correspond à vos critères de recherche.</p>
                                @if(request()->hasAny(['search', 'status', 'service_id']))
                                    <div class="mt-4">
                                        <a href="{{ route('admin.history.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md border border-transparent shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Réinitialiser les filtres
                                        </a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
            {{ $queues->withQueryString()->links() }}
        </div>
    </div>

@endsection
