<div>
    <style>
        [x-cloak] { display: none !important; }
    </style>

    <div class="mx-auto max-w-7xl">
        <div class="flex flex-col gap-6 mb-8 md:flex-row">
            <!-- Statistiques -->
            <div class="grid flex-1 grid-cols-1 gap-4 sm:grid-cols-2">
                <!-- Carte Tickets en attente -->
                <div class="relative p-6 bg-white rounded-lg shadow transition-shadow hover:shadow-md">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium tracking-wider text-gray-500 uppercase">Tickets en attente</p>
                            <p class="mt-1 text-3xl font-bold text-gray-900">{{ $stats['active_tickets'] }}</p>
                            @if($stats['active_tickets'] > 0)
                                <p class="mt-2 text-sm text-gray-500">
                                    <span class="font-medium text-green-600">
                                        <i class="fas fa-arrow-up"></i> {{ $stats['active_tickets'] > 1 ? $stats['active_tickets'] . ' tickets' : '1 ticket' }} en file
                                    </span>
                                </p>
                            @else
                                <p class="mt-2 text-sm text-gray-500">Aucun ticket en attente</p>
                            @endif
                        </div>
                        <div class="flex justify-center items-center w-12 h-12 text-blue-600 bg-blue-100 rounded-full">
                            <i class="text-xl fas fa-ticket-alt"></i>
                        </div>
                    </div>
                </div>

                <!-- Carte Ticket en cours -->
                <div class="relative p-6 bg-white rounded-lg shadow transition-shadow hover:shadow-md">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium tracking-wider text-gray-500 uppercase">Ticket en cours</p>
                            <p class="mt-1 text-3xl font-bold text-gray-900">
                                {{ $this->currentTicket ? $this->currentTicket->code_ticket : 'Aucun' }}
                            </p>
                            @if($this->currentTicket && $this->currentTicket->called_at)
                                <p class="mt-2 text-sm text-gray-500">
                                    Appelé il y a {{ $this->currentTicket->called_at->diffForHumans(null, true) }}
                                </p>
                            @endif
                        </div>
                        <div class="flex justify-center items-center w-12 h-12 text-yellow-600 bg-yellow-100 rounded-full">
                            <i class="text-xl fas fa-user-clock"></i>
                        </div>
                    </div>
                </div>

                <!-- Carte Temps d'attente moyen -->
                <div class="relative p-6 bg-white rounded-lg shadow transition-shadow hover:shadow-md">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium tracking-wider text-gray-500 uppercase">Temps d'attente</p>
                            <p class="mt-1 text-3xl font-bold text-gray-900">
                                {{ $stats['average_wait_time'] ? round($stats['average_wait_time']/60, 1) . ' min' : 'N/A' }}
                            </p>
                            @if($stats['average_wait_time'] > 0)
                                <p class="mt-2 text-sm text-gray-500">
                                    Moyenne sur les {{ $stats['processed_tickets']['total'] }} derniers tickets
                                </p>
                            @endif
                        </div>
                        <div class="flex justify-center items-center w-12 h-12 text-green-600 bg-green-100 rounded-full">
                            <i class="text-xl fas fa-stopwatch"></i>
                        </div>
                    </div>
                </div>

                <!-- Carte Historique des tickets -->
                <a href="{{ route('admin.queues.tickets.history', $queue) }}" class="block relative p-6 bg-white rounded-lg shadow transition-all hover:shadow-md hover:bg-gray-50">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium tracking-wider text-gray-500 uppercase">Tickets traités</p>
                            <p class="mt-1 text-3xl font-bold text-gray-900">{{ $stats['processed_tickets']['total'] }}</p>
                            <div class="mt-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium text-green-800 bg-green-100 rounded-full">
                                    {{ $stats['processed_tickets']['served'] }} validés
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 ml-1 text-xs font-medium text-red-800 bg-red-100 rounded-full">
                                    {{ $stats['processed_tickets']['skipped'] }} absents
                                </span>
                            </div>
                        </div>
                        <div class="flex justify-center items-center w-12 h-12 text-purple-600 bg-purple-100 rounded-full">
                            <i class="text-xl fas fa-history"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- QR Code -->
            @if($queue->code)
            <div class="flex flex-col items-center p-6 bg-white rounded-lg shadow transition-shadow hover:shadow-md">
                <h3 class="mb-3 text-lg font-medium text-gray-900">Accès rapide</h3>
                <div class="p-3 bg-white rounded-lg border border-gray-200">
                    {!! QrCode::size(150)->generate(route('public.queue.show.code', $queue->code)) !!}
                </div>
                <div class="mt-4 text-center">
                    <p class="text-sm font-medium text-gray-500">Code de la file</p>
                    <p class="mt-1 font-mono text-lg font-bold text-gray-900">{{ $queue->code }}</p>
                    <div class="flex flex-col mt-3 space-y-2">
                        <a href="{{ route('public.qrcode.show', $queue->code) }}" target="_blank"
                           class="inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                           onclick="window.open(this.href, 'QRCodeWindow', 'width=900,height=700'); return false;">
                            <i class="mr-2 fas fa-expand"></i> Afficher le QR code
                        </a>
                        <button type="button"
                                onclick="copyQueueLink('{{ route('public.queue.show.code', $queue->code) }}')"
                                class="inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-md border border-gray-300 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="mr-2 far fa-copy"></i> Copier le lien
                        </button>
                        <script>
                            function copyQueueLink(link) {
                                navigator.clipboard.writeText(link).then(() => {
                                    // Afficher une notification plus élégante
                                    const notification = document.createElement('div');
                                    notification.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-md shadow-lg flex items-center';
                                    notification.innerHTML = `
                                        <i class="mr-2 fas fa-check-circle"></i>
                                        <span>Lien copié dans le presse-papier !</span>
                                    `;
                                    document.body.appendChild(notification);

                                    // Faire disparaître la notification après 3 secondes
                                    setTimeout(() => {
                                        notification.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                                        setTimeout(() => notification.remove(), 500);
                                    }, 2000);
                                }).catch(err => {
                                    console.error('Erreur lors de la copie : ', err);
                                    alert('Impossible de copier le lien. Veuillez réessayer.');
                                });
                            }
                        </script>
                    </div>
                </div>
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


        <!-- Ticket en cours -->
        <div class="p-6 mb-6 bg-white rounded-lg shadow" wire:poll.5s>
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-900">
                    <i class="mr-2 text-blue-600 fas fa-ticket-alt"></i>
                    Ticket en cours
                </h2>
                @if($this->currentTicket)
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                            @if($this->currentTicket->status === 'waiting') bg-yellow-100 text-yellow-800
                            @elseif($this->currentTicket->status === 'called') bg-blue-100 text-blue-800
                            @elseif($this->currentTicket->status === 'served') bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            @if($this->currentTicket->status === 'waiting') En attente
                            @elseif($this->currentTicket->status === 'called') En cours d'appel
                            @elseif($this->currentTicket->status === 'served') Traité
                            @else {{ ucfirst($this->currentTicket->status) }}
                            @endif
                        </span>
                    </div>
                @endif
            </div>

            @if($this->currentTicket)
                <div class="p-5 bg-gray-50 rounded-lg border border-gray-100">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <!-- Informations du ticket -->
                        <div class="flex-1">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="flex justify-center items-center w-16 h-16 text-2xl font-bold text-white bg-blue-600 rounded-lg">
                                        {{ $this->currentTicket->code_ticket }}
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        Ticket #{{ $this->currentTicket->code_ticket }}
                                    </p>
                                    @if($this->currentTicket->handled_at)
                                        <p class="mt-1 text-sm text-gray-500">
                                            <i class="mr-1 far fa-clock"></i>
                                            Appelé il y a {{ $this->currentTicket->handled_at->diffForHumans(null, true) }}
                                        </p>
                                    @else
                                        <p class="mt-1 text-sm text-gray-500">
                                            <i class="mr-1 far fa-calendar-plus"></i>
                                            Créé il y a {{ $this->currentTicket->created_at->diffForHumans(null, true) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            @if($this->currentTicket->status === 'waiting')
                                <button
                                    wire:click="quickAction('take')"
                                    class="inline-flex justify-center items-center px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-md shadow-sm transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-75 cursor-not-allowed"
                                >
                                    <i class="mr-2 fas fa-hand-paper"></i>
                                    <span>Prendre en charge</span>
                                    <span wire:loading wire:target="quickAction('take')" class="ml-2">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </span>
                                </button>
                            @endif

                            @if($this->currentTicket->status === 'in_progress' && $this->currentTicket->isHandledBy(auth()->user()))
                                <div class="flex flex-col gap-3 w-full sm:flex-row">
                                    <button
                                        wire:click="quickAction('validate')"
                                        class="inline-flex flex-1 justify-center items-center px-4 py-2.5 text-sm font-medium text-white bg-green-600 rounded-md shadow-sm transition-colors hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                        wire:loading.attr="disabled"
                                        wire:loading.class="opacity-75 cursor-not-allowed"
                                    >
                                        <i class="mr-2 fas fa-check-circle"></i>
                                        <span>Marquer comme traité</span>
                                        <span wire:loading wire:target="quickAction('validate')" class="ml-2">
                                            <i class="fas fa-spinner fa-spin"></i>
                                        </span>
                                    </button>

                                    <button
                                        wire:click="quickAction('absent')"
                                        class="inline-flex flex-1 justify-center items-center px-4 py-2.5 text-sm font-medium text-white bg-red-600 rounded-md shadow-sm transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                        wire:loading.attr="disabled"
                                        wire:loading.class="opacity-75 cursor-not-allowed"
                                    >
                                        <i class="mr-2 fas fa-user-times"></i>
                                        <span>Marquer comme absent</span>
                                        <span wire:loading wire:target="quickAction('absent')" class="ml-2">
                                            <i class="fas fa-spinner fa-spin"></i>
                                        </span>
                                    </button>

                                    <button
                                        wire:click="quickAction('release')"
                                        class="inline-flex flex-1 justify-center items-center px-4 py-2.5 text-sm font-medium text-white bg-gray-600 rounded-md shadow-sm transition-colors hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                                        wire:loading.attr="disabled"
                                        wire:loading.class="opacity-75 cursor-not-allowed"
                                    >
                                        <i class="mr-2 fas fa-undo"></i>
                                        <span>Libérer le ticket</span>
                                        <span wire:loading wire:target="quickAction('release')" class="ml-2">
                                            <i class="fas fa-spinner fa-spin"></i>
                                        </span>
                                    </button>
                                </div>
                            @elseif($this->currentTicket->status === 'in_progress')
                                <div class="p-3 bg-yellow-50 border-l-4 border-yellow-400">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <i class="text-yellow-400 fas fa-user-clock"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-yellow-700">
                                                Ce ticket est en cours de traitement par <span class="font-medium">{{ $this->currentTicket->handler->name ?? 'un autre agent' }}</span>.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="p-8 text-center bg-gray-50 rounded-lg border-2 border-gray-200 border-dashed">
                    <i class="mx-auto text-4xl text-gray-400 fas fa-ticket-alt"></i>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun ticket en attente</h3>
                    <p class="mt-1 text-sm text-gray-500">Tous les tickets ont été traités.</p>
                </div>
            @endif
        </div>

        <!-- En-tête de section -->
        <div class="mb-4">
            <h2 class="text-lg font-semibold text-gray-900">
                <i class="mr-2 text-blue-600 fas fa-list"></i>
                Liste des tickets en attente
            </h2>
        </div>

        <!-- Tableau des tickets -->
        <div class="overflow-hidden bg-white rounded-lg ring-1 ring-black ring-opacity-5 shadow">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                Position
                            </th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                <div class="flex items-center cursor-pointer group" wire:click="sortBy('code_ticket')">
                                    Code
                                    <span class="ml-1">
                                        @if($sortField === 'code_ticket' && $sortDirection === 'asc')
                                            <i class="fas fa-sort-up"></i>
                                        @elseif($sortField === 'code_ticket' && $sortDirection === 'desc')
                                            <i class="fas fa-sort-down"></i>
                                        @else
                                            <i class="text-gray-300 fas fa-sort group-hover:text-gray-400"></i>
                                        @endif
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                <div class="flex items-center cursor-pointer group" wire:click="sortBy('status')">
                                    Statut
                                    <span class="ml-1">
                                        @if($sortField === 'status' && $sortDirection === 'asc')
                                            <i class="fas fa-sort-up"></i>
                                        @elseif($sortField === 'status' && $sortDirection === 'desc')
                                            <i class="fas fa-sort-down"></i>
                                        @else
                                            <i class="text-gray-300 fas fa-sort group-hover:text-gray-400"></i>
                                        @endif
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                <div class="flex items-center cursor-pointer group" wire:click="sortBy('created_at')">
                                    Créé le
                                    <span class="ml-1">
                                        @if($sortField === 'created_at' && $sortDirection === 'asc')
                                            <i class="fas fa-sort-up"></i>
                                        @elseif($sortField === 'created_at' && $sortDirection === 'desc')
                                            <i class="fas fa-sort-down"></i>
                                        @else
                                            <i class="text-gray-300 fas fa-sort group-hover:text-gray-400"></i>
                                        @endif
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                Assigné à
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($tickets as $ticket)
                        <tr class="transition-colors hover:bg-gray-50" x-data="{ showActions: false }" @click.away="showActions = false">
                            <!-- Position -->
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                #{{ $loop->iteration }}
                            </td>

                            <!-- Code du ticket -->
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-sm font-semibold text-gray-700 bg-gray-100 rounded-md">
                                    {{ $ticket->code_ticket }}
                                </span>
                            </td>

                            <!-- Statut -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusConfig = [
                                        'waiting' => ['label' => 'En attente', 'icon' => 'clock', 'color' => 'yellow'],
                                        'called' => ['label' => 'Appelé', 'icon' => 'bell', 'color' => 'blue'],
                                        'handled' => ['label' => 'Assigné', 'icon' => 'user-clock', 'color' => 'blue'],
                                        'in_progress' => ['label' => 'En cours de traitement', 'icon' => 'user-clock', 'color' => 'blue'],
                                        'served' => ['label' => 'Servi', 'icon' => 'check-circle', 'color' => 'green'],
                                        'skipped' => ['label' => 'Passé', 'icon' => 'forward', 'color' => 'gray'],
                                    ][$ticket->status] ?? ['label' => $ticket->status, 'icon' => 'question-circle', 'color' => 'gray'];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $statusConfig['color'] }}-100 text-{{ $statusConfig['color'] }}-800">
                                    <i class="mr-1.5 fas fa-{{ $statusConfig['icon'] }}"></i>
                                    {{ $statusConfig['label'] }}
                                </span>
                            </td>

                            <!-- Date de création -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ $ticket->created_at->format('d/m/Y') }}
                                    <span class="text-xs text-gray-500">
                                        {{ $ticket->created_at->format('H:i') }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500">
                                    il y a {{ $ticket->created_at->diffForHumans(null, true) }}
                                </div>
                            </td>

                            <!-- Assigné à -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($ticket->status === 'in_progress' && $ticket->handler)
                                    <div class="flex items-center">
                                        <div class="flex flex-shrink-0 justify-center items-center w-8 h-8 bg-blue-100 rounded-full">
                                            <span class="text-sm font-medium text-blue-800">{{ substr($ticket->handler->name, 0, 1) }}</span>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">{{ $ticket->handler->name }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ $ticket->handled_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                @elseif($ticket->status === 'in_progress')
                                    <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium text-yellow-800 bg-yellow-100 rounded-full">
                                        <i class="mr-1.5 fas fa-user-clock"></i>
                                        En cours
                                    </span>
                                @else
                                    <span class="text-sm text-gray-500">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($tickets->hasPages())
                <div class="flex justify-between items-center px-6 py-3 bg-gray-50 border-t border-gray-200 sm:px-6">
                    <div class="flex flex-1 justify-between sm:hidden">
                        @if($tickets->onFirstPage())
                            <span class="inline-flex relative items-center px-4 py-2 text-sm font-medium text-gray-300 bg-white rounded-md border border-gray-300 cursor-not-allowed">
                                Précédent
                            </span>
                        @else
                            <button wire:click="previousPage" class="inline-flex relative items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-md border border-gray-300 hover:bg-gray-50">
                                Précédent
                            </button>
                        @endif

                        @if($tickets->hasMorePages())
                            <button wire:click="nextPage" class="inline-flex relative items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white rounded-md border border-gray-300 hover:bg-gray-50">
                                Suivant
                            </button>
                        @else
                            <span class="inline-flex relative items-center px-4 py-2 ml-3 text-sm font-medium text-gray-300 bg-white rounded-md border border-gray-300 cursor-not-allowed">
                                Suivant
                            </span>
                        @endif
                    </div>

                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Affichage de
                                <span class="font-medium">{{ $tickets->firstItem() }}</span>
                                à
                                <span class="font-medium">{{ $tickets->lastItem() }}</span>
                                sur
                                <span class="font-medium">{{ $tickets->total() }}</span>
                                résultats
                            </p>
                        </div>
                        <div>
                            <nav class="inline-flex relative z-0 -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                <!-- Previous Page Link -->
                                @if($tickets->onFirstPage())
                                    <span class="inline-flex relative items-center px-2 py-2 text-sm font-medium text-gray-300 bg-white rounded-l-md border border-gray-300 cursor-not-allowed">
                                        <span class="sr-only">Précédent</span>
                                        <i class="w-5 h-5 fas fa-chevron-left"></i>
                                    </span>
                                @else
                                    <button wire:click="previousPage" class="inline-flex relative items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white rounded-l-md border border-gray-300 hover:bg-gray-50">
                                        <span class="sr-only">Précédent</span>
                                        <i class="w-5 h-5 fas fa-chevron-left"></i>
                                    </button>
                                @endif

                                <!-- Pagination Elements -->
                                @foreach($tickets->links()->elements[0] as $page => $url)
                                    @if($tickets->currentPage() == $page)
                                        <span aria-current="page" class="inline-flex relative z-10 items-center px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-500">
                                            {{ $page }}
                                        </span>
                                    @else
                                        <button wire:click="gotoPage({{ $page }})" class="inline-flex relative items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">
                                            {{ $page }}
                                        </button>
                                    @endif
                                @endforeach

                                <!-- Next Page Link -->
                                @if($tickets->hasMorePages())
                                    <button wire:click="nextPage" class="inline-flex relative items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white rounded-r-md border border-gray-300 hover:bg-gray-50">
                                        <span class="sr-only">Suivant</span>
                                        <i class="w-5 h-5 fas fa-chevron-right"></i>
                                    </button>
                                @else
                                    <span class="inline-flex relative items-center px-2 py-2 text-sm font-medium text-gray-300 bg-white rounded-r-md border border-gray-300 cursor-not-allowed">
                                        <span class="sr-only">Suivant</span>
                                        <i class="w-5 h-5 fas fa-chevron-right"></i>
                                    </span>
                                @endif
                            </nav>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
