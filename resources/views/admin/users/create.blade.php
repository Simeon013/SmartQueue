@extends('layouts.admin')

@section('header', 'Créer un nouvel utilisateur')

@section('content')
<div class="px-8 py-6">
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">Créer un nouvel utilisateur</h2>
            <p class="mt-1 text-sm text-gray-600">Remplissez le formulaire pour ajouter un nouvel utilisateur au système</p>
        </div>
        
        <div class="p-6">
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
                @csrf

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
                                    :value="old('name')" 
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
                                    :value="old('email')" 
                                    required 
                                    autocomplete="email"
                                    placeholder="adresse@exemple.com"
                                />
                            </div>
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

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
                                    <option value="">Sélectionnez un rôle</option>
                                    @foreach(\App\Enums\UserRole::cases() as $role)
                                        @if(!auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || $role->value === 'agent')
                                            <option value="{{ $role->value }}" {{ old('role') === $role->value ? 'selected' : '' }}>
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
                    </div>

                    <!-- Colonne de droite -->
                    <div class="space-y-6">
                        <div>
                            <x-input-label for="password" :value="__('Mot de passe')" class="mb-1" />
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <x-text-input 
                                    id="password" 
                                    class="block w-full pl-10" 
                                    type="password" 
                                    name="password" 
                                    required 
                                    autocomplete="new-password"
                                    placeholder="••••••••"
                                />
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Le mot de passe doit contenir au moins 8 caractères
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
                                    required 
                                    autocomplete="new-password"
                                    placeholder="••••••••"
                                />
                            </div>
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end pt-6 border-t border-gray-200 mt-8">
                            <x-secondary-button type="button" onclick="window.history.back()" class="mr-3">
                                {{ __('Annuler') }}
                            </x-secondary-button>
                            <x-primary-button>
                                {{ __('Créer l\'utilisateur') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
 