@extends('layouts.admin')

@section('header', 'Tableau de bord Super Admin')

@push('styles')
<style>
    .stat-card {
        @apply bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-200 hover:shadow-md;
    }
    .stat-icon {
        @apply p-3 rounded-lg bg-opacity-10 flex-shrink-0;
    }
    .rating-stars {
        @apply flex items-center;
    }
    .star {
        @apply text-yellow-400 w-5 h-5;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Bonjour, {{ $user->name }} 👋</h1>
                <p class="text-gray-500 mt-1">Bienvenue sur votre tableau de bord d'administration</p>
            </div>
            <div class="mt-4 md:mt-0">
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-purple-50 text-purple-700">
                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Super Admin
                </span>
            </div>
        </div>
    </div>

    <!-- Statistiques principales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Carte Files d'attente -->
        <div class="bg-white rounded-lg border border-gray-200 p-5 transition-all duration-200 hover:shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Files d'attente</p>
                    <div class="flex items-baseline space-x-2">
                        <span class="text-2xl font-semibold text-gray-900">{{ $queueStats['active_queues'] }}</span>
                        <span class="text-sm text-gray-500">/ {{ $queueStats['total_queues'] }} total</span>
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
                        <div class="font-medium text-gray-900">{{ $queueStats['active_queues'] }}</div>
                        <div class="text-gray-500">Actives</div>
                    </div>
                    <div class="text-center">
                        <div class="font-medium text-gray-900">{{ $queueStats['total_queues'] - $queueStats['active_queues'] }}</div>
                        <div class="text-gray-500">Inactives</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Carte Utilisateurs -->
        <div class="group relative bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-md hover:-translate-y-1">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Utilisateurs</p>
                        <h3 class="text-2xl font-bold text-gray-900">{{ number_format($userStats['total_users'], 0, ',', ' ') }}</h3>
                    </div>
                    <div class="p-3 rounded-lg bg-purple-50 text-purple-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-6 grid grid-cols-3 gap-4 text-center">
                    <div class="space-y-1">
                        <div class="text-sm font-medium text-purple-700">{{ $userStats['super_admins'] }}</div>
                        <p class="text-xs text-gray-500">Super Admin</p>
                    </div>
                    <div class="space-y-1">
                        <div class="text-sm font-medium text-indigo-600">{{ $userStats['admins'] }}</div>
                        <p class="text-xs text-gray-500">Admins</p>
                    </div>
                    <div class="space-y-1">
                        <div class="text-sm font-medium text-blue-600">{{ $userStats['agents'] }}</div>
                        <p class="text-xs text-gray-500">Agents</p>
                    </div>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-purple-500 to-indigo-600"></div>
        </div>

        <!-- Carte Activité du jour -->
        <div class="group relative bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-md hover:-translate-y-1">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Activité Aujourd'hui</p>
                        <h3 class="text-2xl font-bold text-gray-900">{{ $todayStats['tickets_created'] }}</h3>
                    </div>
                    <div class="p-3 rounded-lg bg-green-50 text-green-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
                <div class="mt-6 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                            <span class="text-sm text-gray-600">Traités</span>
                        </div>
                        <span class="text-sm font-medium text-gray-900">{{ $todayStats['tickets_served'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <span class="w-2 h-2 rounded-full bg-yellow-400 mr-2"></span>
                            <span class="text-sm text-gray-600">En attente</span>
                        </div>
                        <span class="text-sm font-medium text-gray-900">{{ $todayStats['tickets_pending'] }}</span>
                    </div>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-green-500 to-emerald-600"></div>
        </div>

        <!-- Carte Avis clients -->
        <div class="group relative bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-md hover:-translate-y-1">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Avis clients</p>
                        <div class="flex items-baseline">
                            <h3 class="text-2xl font-bold text-gray-900">{{ number_format($reviewStats['average_rating'], 1) }}</h3>
                            <span class="ml-1 text-sm font-medium text-gray-500">/ 5.0</span>
                        </div>
                    </div>
                    <div class="px-2.5 py-1 rounded-full bg-yellow-50 text-yellow-700 text-xs font-medium">
                        {{ $reviewStats['total_reviews'] }} avis
                    </div>
                </div>

                <div class="mt-4 flex items-center space-x-1">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= floor($reviewStats['average_rating']))
                            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        @elseif($i == ceil($reviewStats['average_rating']) && $reviewStats['average_rating'] - floor($reviewStats['average_rating']) > 0)
                            <div class="relative">
                                <div class="absolute overflow-hidden" style="width: {{ ($reviewStats['average_rating'] - floor($reviewStats['average_rating'])) * 100 }}%">
                                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                </div>
                                <svg class="w-5 h-5 text-gray-200" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </div>
                        @else
                            <svg class="w-5 h-5 text-gray-200" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        @endif
                    @endfor
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Satisfaction</span>
                        <span class="font-medium text-gray-900">{{ number_format(($reviewStats['average_rating'] / 5) * 100, 0) }}%</span>
                    </div>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-yellow-400 to-yellow-500"></div>
        </div>
    </div>

    <!-- Section Statistiques de la journée -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Activité du jour</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <p class="text-sm font-medium text-blue-700">Tickets créés</p>
                <p class="text-2xl font-semibold text-blue-900">{{ $todayStats['tickets_created'] }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <p class="text-sm font-medium text-green-700">Tickets traités</p>
                <p class="text-2xl font-semibold text-green-900">{{ $todayStats['tickets_served'] }}</p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <p class="text-sm font-medium text-yellow-700">En attente</p>
                <p class="text-2xl font-semibold text-yellow-900">{{ $todayStats['tickets_pending'] }}</p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <p class="text-sm font-medium text-purple-700">Agents actifs</p>
                <p class="text-2xl font-semibold text-purple-900">{{ $todayStats['active_agents'] }}</p>
            </div>
        </div>
    </div>

    <!-- Section Utilisateurs et Avis -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Carte Utilisateurs -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h2 class="font-medium text-gray-900">Utilisateurs du système</h2>
                    <a href="{{ route('admin.users.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Voir tout</a>
                </div>
            </div>
            <div class="divide-y divide-gray-100">
                <div class="p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium">Super Administrateurs</p>
                                <p class="text-sm text-gray-500">Accès complet au système</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">{{ $userStats['super_admins'] }}</span>
                    </div>
                </div>
                <div class="p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium">Administrateurs</p>
                                <p class="text-sm text-gray-500">Gestion des files et agents</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">{{ $userStats['admins'] }}</span>
                    </div>
                </div>
                <div class="p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium">Agents</p>
                                <p class="text-sm text-gray-500">Gestion des tickets</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">{{ $userStats['agents'] }}</span>
                    </div>
                </div>
                <div class="p-4 border-t border-gray-100">
                    <div class="flex items-center justify-between">
                        <span class="font-medium">Total</span>
                        <span class="px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">{{ $userStats['total_users'] }} utilisateurs</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Carte Avis clients -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h2 class="font-medium text-gray-900">Avis clients</h2>
                    <a href="{{ route('admin.reviews.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Voir tout</a>
                </div>
            </div>
            <div class="p-5">
                @if($reviewStats['total_reviews'] > 0)
                    <div class="text-center py-4">
                        <div class="text-4xl font-bold text-gray-900 mb-2">{{ number_format($reviewStats['average_rating'], 1) }}<span class="text-xl text-gray-500">/5</span></div>
                        <div class="flex justify-center mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= floor($reviewStats['average_rating']))
                                    <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @endif
                            @endfor
                        </div>
                        <p class="text-gray-600">Basé sur {{ $reviewStats['total_reviews'] }} avis</p>
                    </div>

                    <!-- Répartition des notes -->
                    <div class="space-y-2 mt-4">
                        @for($i = 5; $i >= 1; $i--)
                            <div class="flex items-center">
                                <span class="w-8 text-sm text-gray-500">{{ $i }} étoile{{ $i > 1 ? 's' : '' }}</span>
                                <div class="flex-1 h-2 mx-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-yellow-400" style="width: {{ $i * 20 }}%"></div>
                                </div>
                                <span class="w-8 text-sm text-gray-500">{{ $i * 20 }}%</span>
                            </div>
                        @endfor
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                        <h3 class="mt-2 text-lg font-medium text-gray-900">Aucun avis pour le moment</h3>
                        <p class="mt-1 text-gray-500">Les avis de vos clients apparaîtront ici</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Section 3: Derniers utilisateurs et activité récente -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Derniers utilisateurs inscrits -->
        <div class="lg:col-span-2 bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-medium text-gray-900">Derniers utilisateurs inscrits</h2>
                <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                    Voir tout
                </a>
            </div>
            <div class="px-6 py-4">
                @if($recentUsers->isNotEmpty())
                    <div class="flow-root">
                        <ul role="list" class="divide-y divide-gray-200">
                            @foreach($recentUsers as $user)
                                <li class="py-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                {{ $user->name }}
                                            </p>
                                            <p class="text-sm text-gray-500 truncate">
                                                {{ $user->email }}
                                            </p>
                                        </div>
                                        <div>
                                            @if($user->hasRole('super-admin'))
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                    Super Admin
                                                </span>
                                            @elseif($user->hasRole('admin'))
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    Admin
                                                </span>
                                            @elseif($user->hasRole('agent'))
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Agent
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    Utilisateur
                                                </span>
                                            @endif
                                            <p class="text-xs text-gray-500 mt-1">
                                                Inscrit le {{ $user->created_at->format('d/m/Y') }}
                                            </p>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun utilisateur</h3>
                        <p class="mt-1 text-sm text-gray-500">Aucun utilisateur n'est actuellement enregistré dans le système.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Activité récente -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Activité récente</h2>
            </div>
            <div class="px-6 py-4">
                @if(isset($recentActivities) && $recentActivities->count() > 0)
                    <div class="flow-root">
                        <ul role="list" class="divide-y divide-gray-200">
                            @foreach($recentActivities as $activity)
                                <li class="py-4">
                                    <div class="flex space-x-3">
                                        <div class="flex-shrink-0">
                                            <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                                                {{ strtoupper(substr($activity->causer->name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm text-gray-800">
                                                <span class="font-medium text-gray-900">{{ $activity->causer->name }}</span>
                                                {{ $activity->description }}
                                                @if($activity->subject)
                                                    <span class="font-medium">{{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}</span>
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ $activity->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune activité récente</h3>
                        <p class="mt-1 text-sm text-gray-500">Aucune activité n'a été enregistrée pour le moment.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
