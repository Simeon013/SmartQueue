@extends('layouts.admin')

@section('header', "Nouvelle file d'attente")

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <form method="POST" action="{{ route('admin.queues.store') }}" class="p-6 space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700">Établissement</label>
                <input type="text" value="{{ $establishment->name }}" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100" readonly>
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nom de la file</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-300 @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Le statut est défini comme ouvert par défaut -->
            <input type="hidden" name="status" value="open">
            
            <div class="p-3 bg-blue-50 border border-blue-100 rounded-md">
                <p class="text-sm text-blue-800 flex items-start">
                    <svg class="h-5 w-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Nouvelle file d'attente créée avec le statut <span class="ml-1 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Ouverte</span>
                </p>
            </div>

            <!-- Note informative -->
            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h4 class="text-sm font-medium text-blue-800">Configuration des permissions</h4>
                        <p class="mt-1 text-sm text-blue-700">
                            Après la création de la file, le mode "tous les agents - gestion complète" sera automatiquement activé. Vous pourrez ensuite ajuster les permissions selon vos besoins.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.queues.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    Annuler
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Créer la file
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
