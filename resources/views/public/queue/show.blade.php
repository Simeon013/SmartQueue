@extends('layouts.public')

@section('content')
<div class="w-full sm:max-w-md px-6 py-4 mt-6 overflow-hidden sm:rounded-lg text-center">
    <h1 class="text-4xl font-bold text-blue-600 mb-2">SmartQueue</h1>
    <p class="text-gray-600">File d'attente : <span class="font-semibold">{{ $queue->name }}</span></p>
    <p class="text-gray-500 text-sm">{{ $queue->description }}</p>
</div>

<div class="w-full sm:max-w-md mt-6 p-6 bg-white shadow-md overflow-hidden sm:rounded-lg">
    {{-- Le titre "Rejoindre cette file" et le formulaire seront gérés par le composant PublicTicketStatus --}}
    {{-- <h2 class="text-2xl font-semibold text-gray-800 mb-4 text-center">Rejoindre cette file</h2> --}}

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 text-sm" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 text-sm" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Section gérée par le composant Livewire PublicTicketStatus -->
    {{-- Ce composant affiche soit le formulaire pour prendre un ticket, soit les infos du ticket en cours --}}
    <div class="mb-6"> {{-- Ajout mb-6 ici pour espacement --}}
         @livewire('public.public-ticket-status', ['queue' => $queue])
    </div>


    <!-- Section pour afficher les tickets en attente et appelés -->
    {{-- Cette section reste dans la vue parent car elle affiche l'état global de la file --}}
    <div class="border-t pt-6 border-gray-200" wire:poll.2s>
        <h3 class="text-xl font-semibold text-gray-800 mb-4 text-center">Tickets en cours</h3>

        @php
            // Récupérer les tickets actifs (en attente ou appelés) pour cette file, triés par date de création.
            $activeTickets = $queue->tickets()->whereIn('status', ['waiting', 'called'])->orderBy('created_at', 'asc')->get();
        @endphp

        @if($activeTickets->count() > 0)
            <ul class="divide-y divide-gray-200">
                @foreach($activeTickets as $ticket)
                    <li class="py-3 flex justify-between items-center">
                        <div>
                            <span class="text-lg font-mono text-blue-700">{{ $ticket->code_ticket }}</span>
                            <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $ticket->status === 'waiting' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ ucfirst($ticket->status) }}
                            </span>
                        </div>
                        {{-- Afficher la position uniquement pour les tickets en attente --}}
                        @if($ticket->status === 'waiting')
                            @php
                                // Calculer la position basée uniquement sur les tickets "waiting" actuellement listés.
                                // Note: Cette position est relative à cette liste affichée, pas une position globale absolue.
                                $waitingTickets = $activeTickets->filter(fn($t) => $t->status === 'waiting')->sortBy('created_at')->values();
                                $currentPosition = $waitingTickets->search(fn($t) => $t->id === $ticket->id);
                            @endphp
                             @if($currentPosition !== false)
                                <span class="text-sm text-gray-600">Position: {{ $currentPosition + 1 }}</span>
                            @endif
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <div class="text-center py-4 text-gray-500">
                Aucun ticket en attente pour le moment.
            </div>
        @endif
    </div>
</div>
@endsection