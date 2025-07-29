@extends('layouts.admin')

@section('header', 'Paramètres')

@section('content')
<div class="space-y-8">
    <!-- Section Informations de l'établissement -->
    <div class="overflow-hidden bg-white rounded-lg border border-gray-200 shadow-sm">
        <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
            <div class="flex items-center">
                <svg class="mr-3 w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Informations de l'établissement</h3>
                    <p class="mt-1 text-sm text-gray-600">Gérez les informations générales de votre établissement</p>
                </div>
            </div>
        </div>
        <div class="p-6">
            @php
                $establishment = \App\Models\Establishment::first();
            @endphp
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="p-4 bg-gray-50 rounded-lg">
                    <label class="block mb-2 text-sm font-medium text-gray-700">Nom de l'établissement</label>
                    <p class="text-lg font-medium text-gray-900">{{ $establishment->name ?? 'Non défini' }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <label class="block mb-2 text-sm font-medium text-gray-700">Type d'établissement</label>
                    <p class="text-gray-900">{{ $establishment->type ?? 'Non défini' }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <label class="block mb-2 text-sm font-medium text-gray-700">Adresse</label>
                    <p class="text-gray-900">{{ $establishment->address ?? 'Non définie' }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <label class="block mb-2 text-sm font-medium text-gray-700">Ville</label>
                    <p class="text-gray-900">{{ $establishment->city ?? 'Non définie' }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <label class="block mb-2 text-sm font-medium text-gray-700">Code postal</label>
                    <p class="text-gray-900">{{ $establishment->postal_code ?? 'Non défini' }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <label class="block mb-2 text-sm font-medium text-gray-700">Pays</label>
                    <p class="text-gray-900">{{ $establishment->country ?? 'Non défini' }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg md:col-span-2">
                    <label class="block mb-2 text-sm font-medium text-gray-700">Description</label>
                    <p class="text-gray-900">{{ $establishment->description ?? 'Aucune description' }}</p>
                </div>
            </div>
            <div class="flex justify-end mt-6">
                <a href="{{ route('admin.establishment.settings') }}"
                   class="inline-flex items-center px-6 py-3 text-sm font-semibold tracking-wider text-white uppercase bg-blue-600 rounded-lg border border-transparent shadow-sm transition duration-150 ease-in-out hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <svg class="mr-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifier les informations
                </a>
            </div>
        </div>
    </div>

    <!-- Section Gestion des services (Super Admin uniquement) -->
    @if(auth()->user()->isSuperAdmin())
    @php
        $services = \App\Models\Service::orderBy('name')->take(5)->get();
        $totalServices = \App\Models\Service::count();
        $activeServices = \App\Models\Service::where('is_active', true)->count();
    @endphp
    <div class="overflow-hidden bg-white rounded-lg border border-gray-200 shadow-sm">
        <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-indigo-50 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="mr-3 w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                    </svg>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Gestion des services</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ $totalServices }} service(s) enregistré(s) dont {{ $activeServices }} actif(s)
                        </p>
                    </div>
                </div>
                <a href="{{ route('admin.services.index') }}" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-purple-700 bg-white rounded-md border border-purple-200 shadow-sm hover:bg-purple-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                    <svg class="mr-1.5 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                    </svg>
                    Tout voir
                </a>
            </div>
        </div>
        <div class="p-6">
            @if($services->count() > 0)
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($services as $service)
                        <a href="{{ route('admin.services.edit', $service) }}" class="block transition-transform duration-200 transform hover:scale-[1.02]">
                            <div class="flex items-start p-4 bg-white border border-gray-200 rounded-lg shadow-xs hover:shadow-md transition-all duration-200">
                                <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 rounded-md {{ $service->is_active ? 'bg-purple-100 text-purple-600' : 'bg-gray-100 text-gray-400' }}">
                                    <i class="fas fa-{{ $service->icon }}"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="flex items-center">
                                        <h4 class="text-sm font-medium text-gray-900 group-hover:text-purple-600">{{ $service->name }}</h4>
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $service->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $service->is_active ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500 line-clamp-2">{{ $service->description ?: 'Aucune description' }}</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
                @if($totalServices > 5)
                    <div class="mt-4 text-center">
                        <p class="text-sm text-gray-500">
                            {{ $totalServices - 5 }} autre(s) service(s) non affiché(s)
                        </p>
                    </div>
                @endif
            @else
                <div class="p-6 text-center bg-gray-50 rounded-lg">
                    <svg class="mx-auto w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun service</h3>
                    <p class="mt-1 text-sm text-gray-500">Commencez par ajouter votre premier service.</p>
                </div>
            @endif
            <div class="flex justify-end mt-6">
                <a href="{{ route('admin.services.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-md border border-transparent shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                    <svg class="mr-2 -ml-1 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Ajouter un service
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Section Heure de fermeture automatique -->
    <div class="overflow-hidden bg-white rounded-lg border border-gray-200 shadow-sm">
        <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
            <div class="flex items-center">
                <svg class="mr-3 w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Fermeture automatique des files</h3>
                    <p class="mt-1 text-sm text-gray-600">Configurez l'heure de fermeture automatique des files d'attente</p>
                </div>
            </div>
        </div>
        <div class="p-6">
            <!-- Information sur le fonctionnement -->
            <div class="p-4 mb-8 bg-blue-50 rounded-lg border border-blue-200">
                <div class="flex items-start">
                    <svg class="flex-shrink-0 mt-0.5 mr-3 w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <h4 class="text-sm font-medium text-blue-900">Fonctionnement</h4>
                        <p class="mt-1 text-sm text-blue-700">
                            Vos files d'attente se fermeront automatiquement à l'heure et aux jours que vous définissez ci-dessous.
                        </p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.settings.auto-close') }}" class="space-y-8">
                @csrf

                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <div class="space-y-6">
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <label for="auto_close_enabled" class="flex items-center cursor-pointer">
                                <input type="checkbox" id="auto_close_enabled" name="auto_close_enabled" value="1"
                                       class="text-blue-600 rounded border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                       {{ ($autoCloseSettings['auto_close_enabled'] ?? false) ? 'checked' : '' }}>
                                <span class="ml-3 text-sm font-medium text-gray-700">Activer la fermeture automatique</span>
                            </label>
                            <p class="mt-2 ml-6 text-xs text-gray-500">Fermer automatiquement toutes les files à l'heure définie</p>
                        </div>

                        <div class="p-4 bg-gray-50 rounded-lg">
                            <label for="auto_close_time" class="block mb-3 text-sm font-medium text-gray-700">Heure de fermeture</label>
                            <div class="flex items-center space-x-3">
                                <div class="flex-1">
                                    <label for="auto_close_hour" class="block mb-1 text-xs text-gray-500">Heure</label>
                                    <select id="auto_close_hour" name="auto_close_hour"
                                            class="block w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @php
                                            $currentHour = isset($autoCloseSettings['auto_close_time']) ? (int)substr($autoCloseSettings['auto_close_time'], 0, 2) : 18;
                                        @endphp
                                        @for($hour = 0; $hour <= 23; $hour++)
                                            <option value="{{ $hour }}" {{ $hour == $currentHour ? 'selected' : '' }}>
                                                {{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}h
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="flex-1">
                                    <label for="auto_close_minute" class="block mb-1 text-xs text-gray-500">Minute</label>
                                    <select id="auto_close_minute" name="auto_close_minute"
                                            class="block w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @php
                                            $currentMinute = isset($autoCloseSettings['auto_close_time']) ? (int)substr($autoCloseSettings['auto_close_time'], 3, 2) : 0;
                                        @endphp
                                        @for($minute = 0; $minute <= 59; $minute += 5)
                                            <option value="{{ $minute }}" {{ $minute == $currentMinute ? 'selected' : '' }}>
                                                {{ str_pad($minute, 2, '0', STR_PAD_LEFT) }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">Heure à laquelle toutes les files seront fermées (format 24h)</p>
                        </div>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg">
                        <label class="block mb-4 text-sm font-medium text-gray-700">Jours de fermeture</label>
                        <div class="grid grid-cols-2 gap-3">
                            @php
                                $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
                                $selectedDays = $autoCloseSettings['auto_close_days'] ?? [0,1,2,3,4];
                            @endphp
                            @foreach($days as $index => $day)
                                <label class="flex items-center p-2 rounded transition-colors cursor-pointer hover:bg-white">
                                    <input type="checkbox" name="auto_close_days[]" value="{{ $index }}"
                                           class="text-blue-600 rounded border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                           {{ in_array($index, $selectedDays) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-700">{{ $day }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-3 text-xs text-gray-500">Sélectionnez les jours où la fermeture automatique doit être active</p>
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t border-gray-200">
                    <button type="submit"
                            class="inline-flex items-center px-6 py-3 text-sm font-semibold tracking-wider text-white uppercase bg-blue-600 rounded-lg border border-transparent shadow-sm transition duration-150 ease-in-out hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <svg class="mr-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
