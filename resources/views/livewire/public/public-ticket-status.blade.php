<div>
    {{-- Debug: Afficher l'ID de session utilisé par ce composant --}}
    <div class="mb-2 text-xs text-gray-500">Session ID: {{ session()->getId() }}</div>

    @if ($ticket)
        @if ($ticket->status === 'cancelled')
            <div class="p-4 text-red-700 bg-red-100 border-l-4 border-red-500" role="alert">
                <p class="font-bold">Ticket Annulé</p>
                <p>Code: <span class="font-mono text-red-900">{{ $ticket->code_ticket }}</span></p>
                <p class="mt-2">Votre ticket a été annulé.</p>
                <p class="mt-2">Si vous souhaitez rejoindre à nouveau la file, veuillez prendre un nouveau ticket.</p>
            </div>
        @else
            <div class="p-4 text-blue-700 bg-blue-100 border-l-4 border-blue-500" role="alert">
                <p class="font-bold">Votre Ticket</p>
                <p>Code: <span class="font-mono text-blue-900">{{ $ticket->code_ticket }}</span></p>
                <p>Numéro: <span class="font-semibold">{{ $ticket->number }}</span></p>
                <p>Statut: <span class="font-semibold">{{ ucfirst($ticket->status) }}</span></p>

                @if ($ticket->status === 'waiting')
                    <div class="mt-2 space-y-1">
                        <p>Position dans la file: <span class="font-semibold">{{ $position }}</span></p>
                        @if($estimatedWaitTime !== '--:--')
                            <p>Temps d'attente estimé: <span class="font-semibold">{{ $estimatedWaitTime }}</span></p>
                        @endif
                        @if($actualWaitTime !== '--:--')
                            <p>Temps d'attente réel: <span class="font-semibold">{{ $actualWaitTime }}</span></p>
                        @endif
                    </div>
                @elseif ($ticket->status === 'in_progress')
                    <div class="mt-2 space-y-1">
                        <p class="font-semibold text-green-800">Votre ticket est en cours de traitement</p>
                        @if($actualWaitTime !== '--:--')
                            <p>Temps d'attente: <span class="font-semibold">{{ $actualWaitTime }}</span></p>
                        @endif
                    </div>
                @endif
            </div>
        @endif

            {{-- Bouton pour annuler le ticket (optionnel pour MVP, peut être ajouté plus tard) --}}
            {{-- <button wire:click="cancelTicket" class="mt-2 text-sm text-red-600 hover:underline">Annuler le ticket</button> --}}
        </div>
    @else
        {{-- Afficher le formulaire pour prendre un ticket quand aucun ticket n'est en cours --}}
        <div class="py-4 mb-6 text-center text-gray-500">
            <p class="mb-4 font-bold">Aucun ticket en cours</p>
            <p class="mb-4">Utilisez le formulaire ci-dessous pour rejoindre la file.</p>

            <!-- Formulaire pour prendre un ticket -->
            <div class="mb-6">
                 <form method="POST" action="{{ route('public.queue.join', $queue) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="name" class="block text-sm font-medium text-left text-gray-700">Nom (requis)</label>
                        <input type="text" name="name" id="name" required class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-left text-gray-700">Email (optionnel)</label>
                        <input type="email" name="email" id="email" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-left text-gray-700">Téléphone (optionnel)</label>
                        <input type="tel" name="phone" id="phone" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-left text-gray-700">Notes (optionnel)</label>
                        <textarea name="notes" id="notes" rows="3" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"></textarea>
                    </div>

                    <button type="submit" class="inline-flex justify-center items-center px-4 py-2 w-full text-xs font-semibold tracking-widest text-white uppercase bg-blue-600 rounded-md border border-transparent hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Prendre un ticket
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
