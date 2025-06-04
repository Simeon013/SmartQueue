<div>
    <style>
        [x-cloak] { display: none !important; }
    </style>

    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row gap-6 mb-8">
            <!-- Statistiques -->
            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
                    <div class="text-blue-600 text-3xl mb-2"><i class="fas fa-ticket-alt"></i></div>
                    <div class="text-gray-500 text-sm">Total Tickets</div>
                    <div class="text-2xl font-bold">{{ $stats['total_tickets'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
                    <div class="text-yellow-500 text-3xl mb-2"><i class="fas fa-clock"></i></div>
                    <div class="text-gray-500 text-sm">Tickets Actifs</div>
                    <div class="text-2xl font-bold">{{ $stats['active_tickets'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
                    <div class="text-green-600 text-3xl mb-2"><i class="fas fa-hourglass-half"></i></div>
                    <div class="text-gray-500 text-sm">Temps d'attente moyen</div>
                    <div class="text-2xl font-bold">{{ $stats['average_wait_time'] ? round($stats['average_wait_time']/60, 1) . ' min' : 'N/A' }}</div>
                </div>
                <a href="{{ route('admin.queues.tickets.history', $queue) }}" class="bg-white rounded-lg shadow p-6 flex flex-col items-center hover:bg-gray-50 transition">
                    <div class="text-purple-600 text-3xl mb-2"><i class="fas fa-history"></i></div>
                    <div class="text-gray-500 text-sm">Tickets Traités</div>
                    <div class="text-2xl font-bold">{{ $stats['processed_tickets']['total'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">
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
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Nouveau Ticket</h3>
            <form wire:submit.prevent="createTicket">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="ticketName" class="block text-sm font-medium text-gray-700">Nom</label>
                        <input type="text" wire:model="ticketName" id="ticketName" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('ticketName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="ticketEmail" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" wire:model="ticketEmail" id="ticketEmail" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('ticketEmail') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="ticketPhone" class="block text-sm font-medium text-gray-700">Téléphone</label>
                        <input type="tel" wire:model="ticketPhone" id="ticketPhone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('ticketPhone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="ticketNotes" class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea wire:model="ticketNotes" id="ticketNotes" rows="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        @error('ticketNotes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-700 text-white font-semibold rounded-lg shadow hover:bg-blue-800 transition">
                        <i class="fas fa-plus mr-2"></i> Créer le ticket
                    </button>
                </div>
            </form>
        </div>

        <!-- Ticket en cours -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6" wire:poll.5s>
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Ticket en cours</h2>

            @if($this->currentTicket)
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="flex items-center space-x-3">
                                <span class="text-2xl font-bold text-indigo-600">{{ $this->currentTicket->code_ticket }}</span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($this->currentTicket->status === 'waiting') bg-yellow-100 text-yellow-800
                                    @elseif($this->currentTicket->status === 'called') bg-blue-100 text-blue-800
                                    @elseif($this->currentTicket->status === 'served') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($this->currentTicket->status) }}
                                </span>
                            </div>
                            <div class="mt-2 text-sm text-gray-600">
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
                            </div>
                        </div>
                        <div class="flex flex-col space-y-2">
                            <!-- Debug info -->
                            <div class="text-xs text-gray-500 mb-2">
                                Statut actuel: {{ $this->currentTicket->status }}
                            </div>

                            @if($this->currentTicket->status === 'waiting')
                                <button wire:click="quickAction('call')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="fas fa-bell mr-2"></i>Appeler
                                </button>
                            @endif
                            @if($this->currentTicket->status === 'called')
                                <div class="flex flex-col space-y-2">
                                    <button wire:click="quickAction('validate')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        <i class="fas fa-check mr-2"></i>Valider
                                    </button>
                                    <button wire:click="quickAction('absent')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        <i class="fas fa-user-times mr-2"></i>Absent
                                    </button>
                                    <button wire:click="quickAction('recall')" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <i class="fas fa-undo mr-2"></i>Remettre en attente
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-4 text-gray-500">
                    Aucun ticket en attente
                </div>
            @endif
        </div>

        <!-- Tableau des tickets -->
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Créé le</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($tickets as $ticket)
                    <tr class="hover:bg-blue-50 transition">
                        <td class="px-4 py-2 font-mono text-blue-700">{{ $ticket->code_ticket }}</td>
                        <td class="px-4 py-2">{{ $ticket->name }}</td>
                        <td class="px-4 py-2">
                            @if($ticket->status === 'waiting')
                                @php
                                    $position = $tickets->where('status', 'waiting')
                                        ->where('created_at', '<=', $ticket->created_at)
                                        ->count();
                                @endphp
                                <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">{{ $position }}</span>
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
                        <td class="px-4 py-2 text-xs">
                            @if($ticket->email)
                                <div class="flex items-center gap-1"><i class="fas fa-envelope text-blue-500"></i> {{ $ticket->email }}</div>
                            @endif
                            @if($ticket->phone)
                                <div class="flex items-center gap-1"><i class="fas fa-phone text-green-500"></i> {{ $ticket->phone }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-xs text-gray-500">{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-2">
                            <div class="flex gap-2">
                                @if($ticket->status === 'waiting')
                                    <button wire:click="updateTicketStatus({{ $ticket->id }}, 'called')" class="inline-flex items-center px-2 py-1 bg-yellow-100 text-yellow-800 rounded hover:bg-yellow-200 text-xs font-semibold">
                                        <i class="fas fa-bell mr-1"></i>Appeler
                                    </button>
                                @endif
                                @if($ticket->status === 'called')
                                    <button wire:click="updateTicketStatus({{ $ticket->id }}, 'served')" class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 rounded hover:bg-green-200 text-xs font-semibold">
                                        <i class="fas fa-check mr-1"></i>Servi
                                    </button>
                                    <button wire:click="updateTicketStatus({{ $ticket->id }}, 'skipped')" class="inline-flex items-center px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-xs font-semibold">
                                        <i class="fas fa-forward mr-1"></i>Passé
                                    </button>
                                @endif
                                <button wire:click="deleteTicket({{ $ticket->id }})" wire:confirm="Êtes-vous sûr de vouloir supprimer ce ticket ?" class="inline-flex items-center px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 text-xs font-semibold">
                                    <i class="fas fa-trash mr-1"></i>Supprimer
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-400">Aucun ticket trouvé</td>
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
