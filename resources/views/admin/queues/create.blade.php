@extends('layouts.admin')

@section('header', "Nouvelle file d'attente")

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <form method="POST" action="{{ route('admin.queues.store') }}" class="p-6 space-y-6">
            @csrf

            <div>
                <label for="establishment_id" class="block text-sm font-medium text-gray-700">Établissement</label>
                <select id="establishment_id" name="establishment_id" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('establishment_id') border-red-300 @enderror">
                    <option value="">Sélectionnez un établissement</option>
                    @foreach($establishments as $establishment)
                        <option value="{{ $establishment->id }}" {{ old('establishment_id') == $establishment->id ? 'selected' : '' }}>
                            {{ $establishment->name }}
                        </option>
                    @endforeach
                </select>
                @error('establishment_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nom de la file</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-300 @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                <label for="is_active" class="ml-2 block text-sm text-gray-700">File active</label>
                @error('is_active')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
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
