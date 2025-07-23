<div>
    <style>
        [x-cloak] { display: none !important; }
    </style>

    <div class="mx-auto max-w-7xl">
        <div class="flex flex-col gap-6 mb-8 md:flex-row">
            <!-- Statistiques -->
            <div class="grid flex-1 grid-cols-1 gap-4 md:grid-cols-2">
                <div class="flex flex-col items-center p-6 bg-white rounded-lg shadow">
                    <div class="mb-2 text-3xl text-blue-600"><i class="fas fa-ticket-alt"></i></div>
                    <div class="text-sm text-gray-500">TICKETS EN ATTENTE</div>
                    <div class="text-2xl font-bold">{{ $stats['active_tickets'] }}</div>
                </div>
                <div class="flex flex-col items-center p-6 bg-white rounded-lg shadow">
                    <div class="mb-2 text-3xl text-yellow-500"><i class="fas fa-clock"></i></div>
                    <div class="text-sm text-gray-500">TICKET  EN COURS DE TRAITEMENT</div>
                    <div class="text-2xl font-bold">{{ $this->currentTicket ? $this->currentTicket->code_ticket : 'N/A' }}</div>
                </div>
                {{-- <div class="flex flex-col items-center p-6 bg-white rounded-lg shadow">
                    <div class="mb-2 text-3xl text-yellow-500"><i class="fas fa-clock"></i></div>
                    <div class="text-sm text-gray-500">Tickets Actifs</div>
                    <div class="text-2xl font-bold">{{ $stats['active_tickets'] }}</div>
                </div> --}}
                {{-- <div class="flex flex-col items-center p-6 bg-white rounded-lg shadow">
                    <div class="mb-2 text-3xl text-green-600"><i class="fas fa-hourglass-half"></i></div>
                    <div class="text-sm text-gray-500">Ajouter un nouveau ticket</div>
                    <div class="text-2xl font-bold">NOUVEAU TICKET</div>
                </div> --}}
                <div class="flex flex-col items-center p-6 bg-white rounded-lg shadow">
                    <div class="mb-2 text-3xl text-green-600"><i class="fas fa-hourglass-half"></i></div>
                    <div class="text-sm text-gray-500">TEMPS MOYEN D'ATTENTE</div>
                    <div class="text-2xl font-bold">{{ $stats['average_wait_time'] ? round($stats['average_wait_time']/60, 1) . ' min' : 'N/A' }}</div>
                </div>
                <a href="{{ route('admin.queues.tickets.history', $queue) }}" class="flex flex-col items-center p-6 bg-white rounded-lg shadow transition hover:bg-gray-50">
                    <div class="mb-2 text-3xl text-purple-600"><i class="fas fa-history"></i></div>
                    <div class="text-sm text-gray-500">TICKETS TRAITÉS</div>
                    <div class="text-2xl font-bold">{{ $stats['processed_tickets']['total'] }}</div>
                    <div class="mt-1 text-xs text-gray-500">
                        <span class="text-green-600">{{ $stats['processed_tickets']['served'] }} validés</span>
                        <span class="mx-1">•</span>
                        <span class="text-red-600">{{ $stats['processed_tickets']['skipped'] }} absents</span>
                    </div>
                </a>
            </div>
            <!-- QR Code -->
            @if($queue->code)
            <div class="flex flex-col items-center justify-center bg-white rounded-lg shadow p-6 min-w-[200px]">
                <div class="mb-2">Code QR de la file</div>
                <div>{!! QrCode::size(120)->generate(route('public.queue.show.code', $queue->code)) !!}</div>
                <div class="mt-2 text-xs text-gray-500">Code: <span class="font-mono">{{ $queue->code }}</span></div>
            </div>
            @endif
        </div>

        <!-- Formulaire de création de ticket -->
        {{-- <div class="p-6 mb-8 bg-white rounded-lg shadow">
            <h3 class="mb-4 text-lg font-medium text-gray-900">Nouveau Ticket</h3>
            <form wire:submit.prevent="createTicket">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="ticketName" class="block text-sm font-medium text-gray-700">Nom</label>
                        <input type="text" wire:model="ticketName" id="ticketName" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('ticketName') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="ticketEmail" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" wire:model="ticketEmail" id="ticketEmail" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('ticketEmail') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="ticketPhone" class="block text-sm font-medium text-gray-700">Téléphone</label>
                        <input type="tel" wire:model="ticketPhone" id="ticketPhone" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('ticketPhone') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="ticketNotes" class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea wire:model="ticketNotes" id="ticketNotes" rows="1" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        @error('ticketNotes') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex justify-end mt-4">
                    <button type="submit" class="inline-flex items-center px-4 py-2 font-semibold text-white bg-blue-700 rounded-lg shadow transition hover:bg-blue-800">
                        <i class="mr-2 fas fa-plus"></i> Créer le ticket
                    </button>
                </div>
            </form>
        </div> --}}

        {{-- Paramètres de File --}}
        <div class="p-6 mb-6 bg-white rounded-lg shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Paramètres de File</h2>
            {{-- Trois boutons horizontals : OUVRIR LA FILE, FERMER LA FILE, ET METTRE EN PAUSE --}}
            <div class="flex flex-row justify-center space-x-4">
                <button wire:click="openQueue" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md border border-transparent shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="mr-2 fas fa-play"></i> OUVRIR LA FILE
                </button>
                <button wire:click="closeQueue" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md border border-transparent shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <i class="mr-2 fas fa-stop"></i> FERMER LA FILE
                </button>
                <button wire:click="pauseQueue" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-700 rounded-md border border-transparent shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="mr-2 fas fa-pause"></i> METTRE EN PAUSE
                </button>
            </div>
        </div>

        <!-- Ticket en cours -->
        <div class="p-6 mb-6 bg-white rounded-lg shadow-sm" wire:poll.5s>
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Ticket en cours</h2>

            @if($this->currentTicket)
                <div class="p-4 mb-4 bg-gray-50 rounded-lg">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="flex items-center space-x-3">
                                <span class="text-2xl font-bold text-indigo-600">{{ $this->currentTicket->code_ticket }}</span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($this->currentTicket->status === 'en attente') bg-yellow-100 text-yellow-800
                                    @elseif($this->currentTicket->status === 'en cours') bg-blue-100 text-blue-800
                                    @elseif($this->currentTicket->status === 'servis') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($this->currentTicket->status) }}
                                </span>
                            </div>
                            {{-- <div class="mt-2 text-sm text-gray-600">
                                <p><span class="font-medium">Nom:</span> {{ $this->currentTicket->name }}</p>
                                @if($this->currentTicket->email)
                                    <p><span class="font-medium">Email:</span> {{ $this->currentTicket->email }}</p>
                                @endif
                                @if($this->currentTicket->phone)
                                    <p><span class="font-medium">Téléphone:</span> {{ $this->currentTicket->phone }}</p>
                                @endif
                                @if($this->currentTicket->notes)
                                    <p><span class="font-medium">Notes:</span> {{ $this->currentTicket->notes }}</p>
                                @endif
                            </div> --}}
                        </div>
                        <div class="flex flex-col space-y-2">
                            <!-- Debug info -->
                            <div class="mb-2 text-xs text-gray-500">
                                Statut actuel: {{ $this->currentTicket->status }}
                            </div>

                            @if($this->currentTicket->status === 'waiting')
                                <button wire:click="quickAction('call')" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md border border-transparent shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="mr-2 fas fa-bell"></i>SUIVANT
                                </button>
                            @endif
                            @if($this->currentTicket->status === 'called')
                                <div class="flex flex-col space-y-2">
                                    <button wire:click="quickAction('validate')" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md border border-transparent shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        <i class="mr-2 fas fa-check"></i>CONFIRMER PRÉSENCE
                                    </button>
                                    <button wire:click="quickAction('absent')" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md border border-transparent shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <i class="mr-2 fas fa-user-times"></i>SUIVANT
                                    </button>
                                    {{-- <button wire:click="quickAction('recall')" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-md border border-gray-300 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <i class="mr-2 fas fa-undo"></i>Remettre en attente
                                    </button> --}}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="py-4 text-center text-gray-500">
                    Aucun ticket en attente
                </div>
            @endif
        </div>

        <!-- Tableau des tickets -->
        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Code</th>
                        {{-- <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Nom</th> --}}
                        <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Position</th>
                        <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Statut</th>
                        {{-- <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Contact</th> --}}
                        <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Créé le</th>
                        {{-- <th class="px-4 text-xs font-medium tracking-wider text-left text-gray-500 uppercase p y-3">Actions</th> --}}
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($tickets as $ticket)
                    <tr class="transition hover:bg-blue-50">
                        <td class="px-4 py-2 font-mono text-blue-700">{{ $ticket->code_ticket }}</td>
                        {{-- <td class="px-4 py-2">{{ $ticket->name }}</td> --}}
                        <td class="px-4 py-2">
                            @if($ticket->status === 'waiting')
                                @php
                                    $position = $tickets->where('status', 'waiting')
                                        ->where('created_at', '<=', $ticket->created_at)
                                        ->count();
                                @endphp
                                <span class="inline-block px-2 py-1 text-xs text-blue-800 bg-blue-100 rounded">{{ $position }}</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            @php
                                $badgeColors = [
                                    'waiting' => 'bg-yellow-100 text-yellow-800',
                                    'called' => 'bg-blue-100 text-blue-800',
                                    'served' => 'bg-green-100 text-green-800',
                                    'skipped' => 'bg-gray-200 text-gray-600',
                                ];
                            @endphp
                            <span class="inline-block px-2 py-1 rounded text-xs font-semibold {{ $badgeColors[$ticket->status] ?? 'bg-gray-100 text-gray-600' }}">
                                @if($ticket->status === 'waiting') En attente
                                @elseif($ticket->status === 'called') Appelé
                                @elseif($ticket->status === 'served') Servi
                                @elseif($ticket->status === 'skipped') Passé
                                @else {{ $ticket->status }}
                                @endif
                            </span>
                        </td>
                        {{-- <td class="px-4 py-2 text-xs">
                            @if($ticket->email)
                                <div class="flex gap-1 items-center"><i class="text-blue-500 fas fa-envelope"></i> {{ $ticket->email }}</div>
                            @endif
                            @if($ticket->phone)
                                <div class="flex gap-1 items-center"><i class="text-green-500 fas fa-phone"></i> {{ $ticket->phone }}</div>
                            @endif
                        </td> --}}
                        <td class="px-4 py-2 text-xs text-gray-500">{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                        {{-- <td class="px-4 py-2">
                            <div class="flex gap-2">
                                @if($ticket->status === 'waiting')
                                    <button wire:click="updateTicketStatus({{ $ticket->id }}, 'called')" class="inline-flex items-center px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded hover:bg-yellow-200">
                                        <i class="mr-1 fas fa-bell"></i>Appeler
                                    </button>
                                @endif
                                @if($ticket->status === 'called')
                                    <button wire:click="updateTicketStatus({{ $ticket->id }}, 'served')" class="inline-flex items-center px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded hover:bg-green-200">
                                        <i class="mr-1 fas fa-check"></i>Servi
                                    </button>
                                    <button wire:click="updateTicketStatus({{ $ticket->id }}, 'skipped')" class="inline-flex items-center px-2 py-1 text-xs font-semibold text-gray-700 bg-gray-200 rounded hover:bg-gray-300">
                                        <i class="mr-1 fas fa-forward"></i>Passé
                                    </button>
                                @endif
                                <button wire:click="deleteTicket({{ $ticket->id }})" wire:confirm="Êtes-vous sûr de vouloir supprimer ce ticket ?" class="inline-flex items-center px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded hover:bg-red-200">
                                    <i class="mr-1 fas fa-trash"></i>Supprimer
                                </button>
                            </div>
                        </td> --}}
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-gray-400">Aucun ticket trouvé</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $tickets->links() }}
        </div>
    </div>
</div>
