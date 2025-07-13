@extends('layouts.admin')

@section('header', 'Gestion des permissions')

@section('content')
<div class="space-y-6">
    <!-- Informations de l'utilisateur -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Permissions de {{ $user->name }}</h3>
            <p class="text-sm text-gray-500 mt-1">{{ $user->email }}</p>
        </div>
    </div>

    <!-- Permissions via les rôles -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Permissions via les rôles</h3>
            <p class="text-sm text-gray-500 mt-1">Ces permissions sont héritées des rôles attribués à l'utilisateur</p>
        </div>
        <div class="p-6">
            @if($user->roles->count() > 0)
                <div class="space-y-4">
                    @foreach($user->roles as $role)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-medium text-gray-900">{{ $role->name }}</h4>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $role->permissions->count() }} permissions
                                </span>
                            </div>
                            @if($role->permissions->count() > 0)
                                <div class="flex flex-wrap gap-2">
                                    @foreach($role->permissions as $permission)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ $permission->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500">Aucune permission</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-4">Aucun rôle attribué</p>
            @endif
        </div>
    </div>

    <!-- Permissions spécifiques sur les files d'attente -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Permissions sur les files d'attente</h3>
            <p class="text-sm text-gray-500 mt-1">Ces permissions sont spécifiques à certaines files d'attente</p>
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <form method="POST" action="{{ route('admin.queue-permissions.destroy', $permission) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900"
                                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette permission ?')">
                                                Supprimer
                                            </button>
                                        </form>
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
        <a href="{{ route('admin.users.show', $user) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Retour aux détails
        </a>
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Retour à la liste
        </a>
    </div>
</div>
@endsection
