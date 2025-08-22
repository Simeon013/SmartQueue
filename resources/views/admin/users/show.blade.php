@extends('layouts.admin')

@section('header', 'Détails de l\'utilisateur : ' . $user->name)

@section('content')
<div class="px-8 py-6">
    <!-- En-tête avec boutons d'action -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Détails de l'utilisateur</h1>
            <p class="text-sm text-gray-600 mt-1">Gérez les informations et les permissions de cet utilisateur</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-arrow-left mr-2"></i>
                Retour
            </a>
            @can('update', $user)
                <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-edit mr-2"></i>
                    Modifier
                </a>
            @endcan
        </div>
    </div>

    <!-- Carte d'informations de base -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                <i class="fas fa-user-circle text-blue-500 mr-2"></i>
                Informations personnelles
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1">
                    <dt class="text-sm font-medium text-gray-500">Nom complet</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->name }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-sm font-medium text-gray-500">Adresse email</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->email }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-sm font-medium text-gray-500">Date d'inscription</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('d/m/Y à H:i') }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-sm font-medium text-gray-500">Dernière connexion</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($user->last_login_at)
                            {{ $user->last_login_at->format('d/m/Y à H:i') }}
                            <span class="text-gray-500 text-xs">({{ $user->last_login_at->diffForHumans() }})</span>
                        @else
                            <span class="text-yellow-600">Jamais connecté</span>
                        @endif
                    </dd>
                </div>
            </div>
        </div>
    </div>

    <!-- Carte des rôles et permissions -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <i class="fas fa-user-shield text-purple-500 mr-2"></i>
                    Rôle et permissions
                </h3>
                @can('updateRole', $user)
                    <a href="{{ route('admin.users.edit', $user) }}#role" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                        <i class="fas fa-pencil-alt mr-1"></i>
                        Modifier
                    </a>
                @endcan
            </div>
        </div>
        <div class="p-6">
            @php
                $userRole = $user->role;
                $rolePermissions = $userRole ? $userRole->getPermissions() : [];
            @endphp

            @if($userRole)
                <div class="space-y-4">
                    <div class="flex items-start">
                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-50 flex items-center justify-center">
                        <i class="fas fa-user-tag text-indigo-600"></i>
                    </div>
                        <div class="ml-4">
                            <h4 class="text-base font-medium text-gray-900">{{ $userRole->label() }}</h4>
                            <p class="text-sm text-gray-500 mt-1">{{ $userRole->getDescription() }}</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 mt-2">
                                {{ count($rolePermissions) }} permission{{ count($rolePermissions) > 1 ? 's' : '' }}
                            </span>
                        </div>
                    </div>

                    @if(count($rolePermissions) > 0)
                        <div class="mt-4">
                            <h5 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-key mr-2 text-gray-400"></i>
                                Permissions associées
                            </h5>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                @foreach($rolePermissions as $permission)
                                    <div class="flex items-center bg-emerald-50 rounded-md p-2">
                                        <i class="fas fa-check-circle text-emerald-500 mr-2"></i>
                                        <span class="text-sm font-medium text-emerald-800">{{ $permission }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-exclamation-circle text-yellow-400 text-4xl mb-2"></i>
                    <p class="text-gray-500">Aucun rôle attribué à cet utilisateur</p>
                    @can('updateRole', $user)
                        <a href="{{ route('admin.users.edit', $user) }}#role" class="mt-2 inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800">
                            <i class="fas fa-plus-circle mr-1"></i>
                            Attribuer un rôle
                        </a>
                    @endcan
                </div>
            @endif
        </div>
    </div>

    <!-- Carte des permissions sur les files d'attente -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <i class="fas fa-list-ol text-green-500 mr-2"></i>
                    Accès aux files d'attente
                </h3>
                @can('update', $user)
                    <a href="{{ route('admin.users.permissions', $user) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                        <i class="fas fa-edit mr-1"></i>
                        Gérer les accès
                    </a>
                @endcan
            </div>
        </div>
        <div class="p-6">
            @if($user->queuePermissions->count() > 0)
                <div class="overflow-hidden border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <i class="fas fa-list-ol mr-1"></i>
                                    File d'attente
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <i class="fas fa-key mr-1"></i>
                                    Niveau d'accès
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($user->queuePermissions as $permission)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-sky-100 rounded-md flex items-center justify-center">
                                                <i class="{{ $permission->queue->status->value === 'open' ? 'fas fa-play text-green-600' : 'fas fa-pause text-yellow-600' }}"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $permission->queue->name }}
                                                    @if(isset($permission->is_global) && $permission->is_global)
                                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                            <i class="fas fa-globe-americas mr-1"></i>
                                                            Permission globale
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-sm text-gray-500">ID: {{ $permission->queue->id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $permissionType = $permission->permission_type ?? $permission->permission;
                                        @endphp
                                        @switch($permissionType)
                                            @case('manager')
                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    <i class="fas fa-cog mr-1"></i>
                                                    Gestion complète
                                                </span>
                                                @break
                                            @case('operator')
                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-ticket-alt mr-1"></i>
                                                    Gestion des tickets
                                                </span>
                                                @break
                                            @case('owner')
                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-user mr-1"></i>
                                                    Propriétaire
                                                </span>
                                                @break
                                            @case('view')
                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-800">
                                                    <i class="fas fa-eye mr-1"></i>
                                                    Lecture seule
                                                </span>
                                                @break
                                            @default
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-slate-100 text-slate-800">
                                                    <i class="fas fa-question-circle mr-1"></i>
                                                    {{ $permissionType }}
                                                </span>
                                        @endswitch
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="mt-2 text-xs text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    {{ $user->name }} a accès à {{ $user->queuePermissions->count() }} file{{ $user->queuePermissions->count() > 1 ? 's' : '' }} d'attente
                </p>
            @else
                <div class="text-center py-8">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100">
                        <i class="fas fa-list text-gray-400 text-xl"></i>
                    </div>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune permission spécifique</h3>
                    <p class="mt-1 text-sm text-gray-500">Cet utilisateur n'a pas d'accès spécifique aux files d'attente.</p>
                    @can('update', $user)
                        <div class="mt-6">
                            <a href="{{ route('admin.users.permissions', $user) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-plus-circle mr-2 -ml-1"></i>
                                Ajouter des permissions
                            </a>
                        </div>
                    @endcan
                </div>
            @endif
        </div>
    </div>

    <!-- Actions -->
    <div class="flex justify-between items-center">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Retour à la liste
        </a>
        <div class="flex space-x-3">
            <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Modifier
            </a>
            @if($user->id !== auth()->id())
                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                        Supprimer
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
