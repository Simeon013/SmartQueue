@extends('layouts.admin')

@section('header', 'Modifier la file d\'attente')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <form method="POST" action="{{ route('admin.queues.update', $queue) }}" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700">Établissement</label>
                <input type="text" value="{{ $establishment->name }}" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100" readonly>
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nom de la file</label>
                <input type="text" id="name" name="name" value="{{ old('name', $queue->name) }}" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-300 @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Statut de la file</label>
                <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm">
                    @foreach(\App\Enums\QueueStatus::cases() as $status)
                        <option value="{{ $status->value }}" 
                                {{ old('status', $queue->status) === $status->value ? 'selected' : '' }}
                                class="flex items-center">
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
                @error('status')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                
                <div class="mt-2 p-3 bg-gray-50 rounded-md">
                    <p class="text-sm text-gray-600">
                        <span class="font-medium">Statut actuel :</span>
                        <span class="ml-2 px-2.5 py-0.5 inline-flex items-center text-xs font-medium rounded-full 
                            {{ 
                                $queue->status === 'open' ? 'bg-green-100 text-green-800' :
                                ($queue->status === 'paused' ? 'bg-yellow-100 text-yellow-800' :
                                ($queue->status === 'blocked' ? 'bg-orange-100 text-orange-800' :
                                'bg-gray-100 text-gray-800'))
                            }}">
                            <span class="w-2 h-2 rounded-full mr-1.5
                                {{ 
                                    $queue->status === 'open' ? 'bg-green-400' :
                                    ($queue->status === 'paused' ? 'bg-yellow-400' :
                                    ($queue->status === 'blocked' ? 'bg-orange-400' :
                                    'bg-gray-400'))
                                }}"></span>
                            {{ $queue->status_label }}
                        </span>
                    </p>
                    @if($queue->status === 'paused')
                        <p class="mt-1 text-xs text-yellow-700">
                            <svg class="inline-block h-4 w-4 mr-1 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            La file est en pause. Les nouveaux tickets ne seront pas acceptés.
                        </p>
                    @elseif($queue->status === 'blocked')
                        <p class="mt-1 text-xs text-orange-700">
                            <svg class="inline-block h-4 w-4 mr-1 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            La file est bloquée. Aucune action n'est possible.
                        </p>
                    @elseif($queue->status === 'closed')
                        <p class="mt-1 text-xs text-gray-700">
                            <svg class="inline-block h-4 w-4 mr-1 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            La file est fermée. Aucune modification n'est possible.
                        </p>
                    @endif
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.queues.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    Annuler
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Mettre à jour
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
