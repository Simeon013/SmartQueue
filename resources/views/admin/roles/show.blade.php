@extends('layouts.admin')

@section('header', 'Détails du rôle')

@section('content')
<div class="space-y-6">
    <!-- Informations de base -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Informations du rôle</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Nom</h4>
                    <p class="mt-1 text-sm text-gray-900">{{ $role->name }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Slug</h4>
                    <p class="mt-1 text-sm text-gray-900">{{ $role->slug }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Description</h4>
                    <p class="mt-1 text-sm text-gray-900">{{ $role->description ?: 'Aucune description' }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Date de création</h4>
                    <p class="mt-1 text-sm text-gray-900">{{ $role->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Permissions -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Permissions attribuées</h3>
                <a href="{{ route('admin.roles.edit', $role) }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                    Modifier les permissions
                </a>
            </div>
        </div>
        <div class="p-6">
            @if($role->permissions->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($role->permissions as $permission)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-sm font-medium text-gray-900">{{ $permission->name }}</h4>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ $permission->slug }}
                                </span>
                            </div>
                            @if($permission->description)
                                <p class="text-sm text-gray-500">{{ $permission->description }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-4">Aucune permission attribuée</p>
            @endif
        </div>
    </div>

    <!-- Utilisateurs ayant ce rôle -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Utilisateurs avec ce rôle</h3>
                <a href="{{ route('admin.roles.users', $role) }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                    Voir tous les utilisateurs
                </a>
            </div>
        </div>
        <div class="p-6">
            @if($role->users->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Utilisateur
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Email
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date d'inscription
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($role->users->take(5) as $user)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-gray-700">
                                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $user->email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $user->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('admin.users.show', $user) }}" class="text-blue-600 hover:text-blue-900">
                                            Voir
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($role->users->count() > 5)
                    <div class="mt-4 text-center">
                        <p class="text-sm text-gray-500">
                            Affichage de 5 utilisateurs sur {{ $role->users->count() }} au total
                        </p>
                    </div>
                @endif
            @else
                <p class="text-gray-500 text-center py-4">Aucun utilisateur avec ce rôle</p>
            @endif
        </div>
    </div>

    <!-- Actions -->
    <div class="flex justify-between items-center">
        <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Retour à la liste
        </a>
        <div class="flex space-x-3">
            <a href="{{ route('admin.roles.edit', $role) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Modifier
            </a>
            @if(!in_array($role->slug, ['super-admin', 'admin', 'agent-manager', 'agent']))
                <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce rôle ?')">
                        Supprimer
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
