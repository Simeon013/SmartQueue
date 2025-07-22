@extends('layouts.admin')

@section('header', 'Gestion des files d\'attente')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">Liste des files d'attente</h3>
            <a href="{{ route('admin.queues.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nouvelle file
            </a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tickets en attente</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($queues as $queue)
                    <tr class="hover:bg-blue-50 cursor-pointer relative">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $queue->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $queue->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $queue->is_active ? 'Ouverte' : 'Fermée' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $queue->tickets()->where('status', 'waiting')->count() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <a href="{{ route('admin.queues.show', $queue) }}" class="text-blue-600 hover:text-blue-900">Voir</a>
                            
                            @php
                                $user = auth()->user();
                                $canManage = $user->hasRole('super-admin') || 
                                            $user->hasRole('admin') || 
                                            $queue->userCanManage($user);
                                $canDelete = $user->hasRole('super-admin') || 
                                            $user->hasRole('admin') || 
                                            $queue->userOwns($user);
                            @endphp
                            
                            @if($canManage)
                                <a href="{{ route('admin.queues.edit', $queue) }}" class="text-yellow-600 hover:text-yellow-900">Modifier</a>
                                <a href="{{ route('admin.queues.permissions', $queue) }}" class="text-green-600 hover:text-green-900">Gérer</a>
                            @endif
                            
                            @if($canDelete)
                                <form action="{{ route('admin.queues.destroy', $queue) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" " 
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette file ?')">
                                        Supprimer
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            Aucune file d'attente trouvée
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($queues->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $queues->links() }}
        </div>
    @endif
</div>
@endsection
