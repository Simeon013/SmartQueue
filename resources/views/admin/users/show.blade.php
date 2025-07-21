@extends('layouts.admin')

@section('header', 'Détails de l\'utilisateur')

@section('content')
<div class="space-y-6">
    <!-- Informations de base -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Informations de l'utilisateur</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Nom</h4>
                    <p class="mt-1 text-sm text-gray-900">{{ $user->name }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Email</h4>
                    <p class="mt-1 text-sm text-gray-900">{{ $user->email }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Date de création</h4>
                    <p class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Dernière connexion</h4>
                    <p class="mt-1 text-sm text-gray-900">
                        {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Jamais connecté' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Rôle -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Rôle attribué</h3>
                <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                    Modifier le rôle
                </a>
            </div>
        </div>
        <div class="p-6">
            @php
                $userRole = $user->role;
                $rolePermissions = $userRole ? $userRole->getPermissions() : [];
            @endphp
            
            @if($userRole)
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">{{ $userRole->label() }}</h4>
                            <p class="text-sm text-gray-500 mt-1">{{ $userRole->getDescription() }}</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ count($rolePermissions) }} permissions
                        </span>
                    </div>
                    
                    <!-- Affichage des permissions du rôle -->
                    @if(count($rolePermissions) > 0)
                        <div class="mt-4">
                            <h5 class="text-sm font-medium text-gray-700 mb-2">Permissions associées :</h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                @foreach($rolePermissions as $permission)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-green-100 text-green-800">
                                        {{ $permission }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <p class="text-gray-500 text-center py-4">Aucun rôle attribué</p>
            @endif
        </div>
    </div>

    <!-- Permissions sur les files d'attente -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Permissions sur les files d'attente</h3>
                <a href="{{ route('admin.users.permissions', $user) }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                    Gérer les permissions
                </a>
            </div>
        </div>
        <div class="p-6">
            @if($user->queuePermissions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    File d'attente
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Permission
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Attribuée le
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($user->queuePermissions as $permission)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $permission->queue->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $permission->queue->code }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($permission->permission_type === 'manage') bg-green-100 text-green-800
                                            @elseif($permission->permission_type === 'manage_tickets') bg-blue-100 text-blue-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst(str_replace('_', ' ', $permission->permission_type)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $permission->created_at->format('d/m/Y H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-4">Aucune permission spécifique sur les files d'attente</p>
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
