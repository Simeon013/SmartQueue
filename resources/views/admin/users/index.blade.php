@extends('layouts.admin')

@section('header', 'Gestion des Utilisateurs')

@section('content')
<div class="px-8 py-6">
    @if(!isset($users) || is_null($users))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <strong class="font-bold">Erreur !</strong>
            <span class="block sm:inline">Impossible de charger la liste des utilisateurs. Veuillez réessayer plus tard.</span>
        </div>
    @else
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-800">Liste des utilisateurs</h2>
            @can('create', \App\Models\User::class)
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-wider hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nouvel utilisateur
            </a>
            @endcan
        </div>

        <!-- Filtres et recherche -->
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                           placeholder="Rechercher un utilisateur...">
                </div>
                {{-- Uniquement super-admin --}}
                @if(Auth::user()->isSuperAdmin())
                <div class="w-full md:w-48">
                    <select name="role" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="">Tous les rôles</option>
                        @foreach(\App\Enums\UserRole::cases() as $role)
                            <option value="{{ $role->value }}" {{ request('role') == $role->value ? 'selected' : '' }}>
                                {{ ucfirst($role->value) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                <button type="submit" class="px-4 py-2 bg-blue-700 text-white rounded-md hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <i class="fas fa-search mr-2"></i>Filtrer
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Utilisateur
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Rôle
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Email
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date de création
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                        <span class="text-sm font-medium text-gray-700">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-500">
                                        @if($user->isSuperAdmin())
                                            Super Administrateur
                                        @elseif($user->isAdmin())
                                            Administrateur
                                        @else
                                            Agent
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($user->isSuperAdmin())
                                    bg-purple-100 text-purple-800
                                @elseif($user->isAdmin())
                                    bg-blue-100 text-blue-800
                                @else
                                    bg-green-100 text-green-800
                                @endif">
                                {{ $user->role->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $user->email }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->created_at->translatedFormat('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                            <div class="flex justify-end space-x-2">
                                <a href="{{ route('admin.users.show', $user) }}"
                                   class="text-gray-500 hover:text-gray-700 p-1 rounded-full hover:bg-gray-100"
                                   title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('update', $user)
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="text-blue-600 hover:text-blue-800 p-1 rounded-full hover:bg-blue-50"
                                   title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('delete', $user)
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-600 hover:text-red-800 p-1 rounded-full hover:bg-red-50"
                                            title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-users-slash text-4xl mb-2 block"></i>
                                <p class="text-gray-700 font-medium">Aucun utilisateur trouvé</p>
                                <p class="text-sm mt-1">Essayez de modifier vos filtres de recherche</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $users->withQueryString()->links() }}
        </div>
    @endif
</div>
@endif
@endsection
