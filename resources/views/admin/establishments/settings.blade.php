@extends('layouts.admin')

@section('header', "Paramètres de l'établissement")

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <form method="POST" action="{{ route('admin.establishment.settings.update') }}" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nom de l'établissement</label>
                <input type="text" name="name" id="name"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-300 @enderror"
                       value="{{ old('name', $establishment->name) }}" required>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-gray-700">Adresse</label>
                <input type="text" name="address" id="address"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('address') border-red-300 @enderror"
                       value="{{ old('address', $establishment->address) }}">
                @error('address')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700">Ville</label>
                    <input type="text" name="city" id="city"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('city') border-red-300 @enderror"
                           value="{{ old('city', $establishment->city) }}">
                    @error('city')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="postal_code" class="block text-sm font-medium text-gray-700">Code postal</label>
                    <input type="text" name="postal_code" id="postal_code"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('postal_code') border-red-300 @enderror"
                           value="{{ old('postal_code', $establishment->postal_code) }}">
                    @error('postal_code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="type" class="block text-sm font-medium text-gray-700">Type d'établissement</label>
                <select name="type" id="type"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('type') border-red-300 @enderror">
                    <option value="">Sélectionnez un type</option>
                    <option value="Restaurant" {{ old('type', $establishment->type) == 'Restaurant' ? 'selected' : '' }}>Restaurant</option>
                    <option value="Banque" {{ old('type', $establishment->type) == 'Banque' ? 'selected' : '' }}>Banque</option>
                    <option value="Hôpital" {{ old('type', $establishment->type) == 'Hôpital' ? 'selected' : '' }}>Hôpital</option>
                    <option value="Administration" {{ old('type', $establishment->type) == 'Administration' ? 'selected' : '' }}>Administration</option>
                    <option value="Autre" {{ old('type', $establishment->type) == 'Autre' ? 'selected' : '' }}>Autre</option>
                </select>
                @error('type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="country" class="block text-sm font-medium text-gray-700">Pays</label>
                <input type="text" name="country" id="country"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('country') border-red-300 @enderror"
                       value="{{ old('country', $establishment->country) }}">
                @error('country')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="3"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('description') border-red-300 @enderror">{{ old('description', $establishment->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-3">
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection 