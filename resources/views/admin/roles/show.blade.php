@extends('layouts.admin')

@section('header', 'Détails du rôle')

@section('content')
@php
    $roleEnum = \App\Enums\UserRole::from($roleData->slug);
@endphp

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
                    <p class="mt-1 text-sm text-gray-900">{{ $roleEnum->label() }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Valeur</h4>
                    <p class="mt-1 text-sm text-gray-900">{{ $roleEnum->value }}</p>
                </div>
                <div class="md:col-span-2">
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Description</h4>
                    <p class="mt-1 text-sm text-gray-900">{{ $roleEnum->getDescription() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Permissions -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Permissions attribuées</h3>
            <p class="mt-1 text-sm text-gray-500">Les permissions sont gérées de manière statique dans le code.</p>
        </div>
        <div class="p-6">
            @php
                $permissions = $roleEnum->getPermissions();
            @endphp

            @if(count($permissions) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($permissions as $permission)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-sm font-medium text-gray-900">{{ $permission }}</h4>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ $permission }}
                                </span>
                            </div>
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
                <a href="{{ route('admin.roles.users', $roleEnum->value) }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                    Voir tous les utilisateurs
                </a>
            </div>
        </div>
        <div class="p-6">
            @php
                $users = \App\Models\User::where('role', $roleEnum->value)->get();
            @endphp

            @if($users->count() > 0)
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
                            @foreach($users->take(5) as $user)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $user->role->label() }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $user->email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $user->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('admin.users.show', $user) }}" class="text-blue-600 hover:text-blue-900">Voir</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($users->count() > 5)
                    <div class="mt-4 text-center">
                        <a href="{{ route('admin.roles.users', $roleEnum->value) }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                            Voir les {{ $users->count() - 5 }} autres utilisateurs...
                        </a>
                    </div>
                @endif
            @else
                <p class="text-gray-500 text-center py-4">Aucun utilisateur avec ce rôle</p>
            @endif
        </div>
    </div>

    <div class="flex justify-start">
        <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Retour à la liste
        </a>
    </div>
</div>
@endsection
