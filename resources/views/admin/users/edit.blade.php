@extends('layouts.admin')

@section('header', 'Modifier l\'utilisateur : ' . $user->name)

@section('content')
<div class="px-8 py-6">
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">Modifier l'utilisateur</h2>
            <p class="mt-1 text-sm text-gray-600">Mettez à jour les informations de l'utilisateur</p>
        </div>
        
        <div class="p-6">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Colonne de gauche -->
                    <div class="space-y-6">
                        <div>
                            <x-input-label for="name" :value="__('Nom complet')" class="mb-1" />
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <x-text-input 
                                    id="name" 
                                    class="block w-full pl-10" 
                                    type="text" 
                                    name="name" 
                                    :value="old('name', $user->name)" 
                                    required 
                                    autofocus 
                                    autocomplete="name"
                                    placeholder="Nom et prénom"
                                />
                            </div>
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="email" :value="__('Adresse email')" class="mb-1" />
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <x-text-input 
                                    id="email" 
                                    class="block w-full pl-10" 
                                    type="email" 
                                    name="email" 
                                    :value="old('email', $user->email)" 
                                    required 
                                    autocomplete="email"
                                    placeholder="adresse@exemple.com"
                                />
                            </div>
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        @can('updateRole', $user)
                        <div>
                            <x-input-label for="role" :value="__('Rôle')" class="mb-1" />
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user-tag text-gray-400"></i>
                                </div>
                                <select 
                                    id="role" 
                                    name="role" 
                                    class="block w-full pl-10 border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 rounded-md shadow-sm" 
                                    required
                                >
                                    @foreach(\App\Enums\UserRole::cases() as $role)
                                        @if(!auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || $role->value === 'agent')
                                            <option value="{{ $role->value }}" {{ old('role', $user->role->value) === $role->value ? 'selected' : '' }}>
                                                {{ $role->label() }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Sélectionnez le rôle approprié pour cet utilisateur
                            </p>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>
                        @else
                            <input type="hidden" name="role" value="{{ $user->role->value }}">
                        @endcan
                    </div>

                    <!-- Colonne de droite -->
                    <div class="space-y-6">
                        <div>
                            <x-input-label for="password" :value="__('Nouveau mot de passe')" class="mb-1" />
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <x-text-input 
                                    id="password" 
                                    class="block w-full pl-10" 
                                    type="password" 
                                    name="password" 
                                    autocomplete="new-password"
                                    placeholder="Laissez vide pour ne pas changer"
                                />
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Laissez vide pour conserver le mot de passe actuel
                            </p>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="password_confirmation" :value="__('Confirmer le mot de passe')" class="mb-1" />
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <x-text-input 
                                    id="password_confirmation" 
                                    class="block w-full pl-10" 
                                    type="password" 
                                    name="password_confirmation" 
                                    autocomplete="new-password"
                                    placeholder="••••••••"
                                />
                            </div>
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        @can('delete', $user)
                        <div class="pt-4 mt-4 border-t border-gray-200">
                            <p class="text-sm font-medium text-gray-700 mb-2">Zone dangereuse</p>
                            <button type="button" 
                                    onclick="if(confirm('Êtes-vous sûr de vouloir supprimer définitivement cet utilisateur ? Cette action est irréversible.')) { document.getElementById('delete-user-form').submit(); }" 
                                    class="text-red-600 hover:text-red-800 text-sm font-medium inline-flex items-center">
                                <i class="fas fa-trash-alt mr-2"></i>
                                Supprimer cet utilisateur
                            </button>
                        </div>
                        @endcan
                    </div>
                </div>

                <div class="flex items-center justify-end pt-6 border-t border-gray-200 mt-8">
                    <x-secondary-button type="button" onclick="window.history.back()" class="mr-3">
                        {{ __('Annuler') }}
                    </x-secondary-button>
                    <x-primary-button>
                        <i class="fas fa-save mr-2"></i>
                        {{ __('Enregistrer les modifications') }}
                    </x-primary-button>
                </div>
            </form>

            @can('delete', $user)
            <form id="delete-user-form" method="POST" action="{{ route('admin.users.destroy', $user) }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>
            @endcan
        </div>
    </div>
</div>
@endsection
