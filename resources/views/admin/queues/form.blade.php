@extends('layouts.admin')

@section('header', isset($queue) ? 'Modifier la file d\'attente' : 'Nouvelle file d\'attente')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow">
        <form action="{{ isset($queue) ? route('admin.queues.update', $queue) : route('admin.queues.store') }}" method="POST" class="p-6">
            @csrf
            @if(isset($queue))
                @method('PUT')
            @endif

            <div class="space-y-6">
                <!-- Nom -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nom</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $queue->name ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                {{-- <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', $queue->description ?? '') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div> --}}

                <!-- Statut -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Statut</label>
                    <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="active" {{ old('status', $queue->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="paused" {{ old('status', $queue->status ?? '') == 'paused' ? 'selected' : '' }}>En pause</option>
                        <option value="closed" {{ old('status', $queue->status ?? '') == 'closed' ? 'selected' : '' }}>Fermée</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Boutons -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.queues.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        Annuler
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ isset($queue) ? 'Mettre à jour' : 'Créer' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
