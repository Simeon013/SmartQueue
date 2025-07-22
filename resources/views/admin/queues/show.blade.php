@extends('layouts.admin')

@php
    use SimpleSoftwareIO\QrCode\Facades\QrCode;
@endphp

@section('header', "Détail de la file d'attente")

@section('content')
    <livewire:admin.queue-tickets :queue="$queue" />
    
    @php
        $user = auth()->user();
        $canManage = $user->hasRole('super_admin') || 
                    $user->hasRole('admin') || 
                    $queue->userCanManage($user);
        $canDelete = $user->hasRole('super_admin') || 
                    $user->hasRole('admin') || 
                    $queue->userOwns($user);
    @endphp
    
    <div class="flex space-x-3">
        @if($canManage)
            <a href="{{ route('admin.queues.edit', $queue) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                Modifier la file
            </a>
            
            <a href="{{ route('admin.queues.manage', $queue) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Gérer les accès
            </a>
        @endif
        
        @if($canDelete)
            <form action="{{ route('admin.queues.destroy', $queue) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" 
                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette file d\'attente ?')">
                    Supprimer la file
                </button>
            </form>
        @endif
    </div>
@endsection
