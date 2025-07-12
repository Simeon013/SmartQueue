@extends('layouts.admin')

@section('header', 'Paramètres')

@section('content')
<div class="space-y-8">
    <!-- Section Informations de l'établissement -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
            <div class="flex items-center">
                <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Informations de l'établissement</h3>
                    <p class="text-sm text-gray-600 mt-1">Gérez les informations générales de votre établissement</p>
                </div>
            </div>
        </div>
        <div class="p-6">
            @php
                $establishment = \App\Models\Establishment::first();
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nom de l'établissement</label>
                    <p class="text-gray-900 font-medium text-lg">{{ $establishment->name ?? 'Non défini' }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type d'établissement</label>
                    <p class="text-gray-900">{{ $establishment->type ?? 'Non défini' }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Adresse</label>
                    <p class="text-gray-900">{{ $establishment->address ?? 'Non définie' }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ville</label>
                    <p class="text-gray-900">{{ $establishment->city ?? 'Non définie' }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Code postal</label>
                    <p class="text-gray-900">{{ $establishment->postal_code ?? 'Non défini' }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pays</label>
                    <p class="text-gray-900">{{ $establishment->country ?? 'Non défini' }}</p>
                </div>
                <div class="md:col-span-2 bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <p class="text-gray-900">{{ $establishment->description ?? 'Aucune description' }}</p>
                </div>
            </div>
            <div class="mt-6 flex justify-end">
                <a href="{{ route('admin.establishment.settings') }}"
                   class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wider hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifier les informations
                </a>
            </div>
        </div>
    </div>

    <!-- Section Heure de fermeture automatique -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
            <div class="flex items-center">
                <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Fermeture automatique des files</h3>
                    <p class="text-sm text-gray-600 mt-1">Configurez l'heure de fermeture automatique des files d'attente</p>
                </div>
            </div>
        </div>
        <div class="p-6">
            <!-- Information sur le fonctionnement -->
            <div class="mb-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <h4 class="text-sm font-medium text-blue-900">Fonctionnement</h4>
                        <p class="text-sm text-blue-700 mt-1">
                            Vos files d'attente se fermeront automatiquement à l'heure et aux jours que vous définissez ci-dessous.
                        </p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.settings.auto-close') }}" class="space-y-8">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label for="auto_close_enabled" class="flex items-center cursor-pointer">
                                <input type="checkbox" id="auto_close_enabled" name="auto_close_enabled" value="1"
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                       {{ ($autoCloseSettings['auto_close_enabled'] ?? false) ? 'checked' : '' }}>
                                <span class="ml-3 text-sm font-medium text-gray-700">Activer la fermeture automatique</span>
                            </label>
                            <p class="text-xs text-gray-500 mt-2 ml-6">Fermer automatiquement toutes les files à l'heure définie</p>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label for="auto_close_time" class="block text-sm font-medium text-gray-700 mb-3">Heure de fermeture</label>
                            <input type="time" id="auto_close_time" name="auto_close_time"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-lg"
                                   value="{{ $autoCloseSettings['auto_close_time'] ?? '18:00' }}">
                            <p class="text-xs text-gray-500 mt-2">Heure à laquelle toutes les files seront fermées</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-gray-700 mb-4">Jours de fermeture</label>
                        <div class="grid grid-cols-2 gap-3">
                            @php
                                $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
                                $selectedDays = $autoCloseSettings['auto_close_days'] ?? [0,1,2,3,4];
                            @endphp
                            @foreach($days as $index => $day)
                                <label class="flex items-center p-2 rounded hover:bg-white transition-colors cursor-pointer">
                                    <input type="checkbox" name="auto_close_days[]" value="{{ $index }}"
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                           {{ in_array($index, $selectedDays) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-700">{{ $day }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-500 mt-3">Sélectionnez les jours où la fermeture automatique doit être active</p>
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t border-gray-200">
                    <button type="submit"
                            class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wider hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Enregistrer les paramètres
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
