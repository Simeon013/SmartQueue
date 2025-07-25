<div wire:poll.2s class="space-y-4">
    <!-- Position Banner -->
    <div class="p-6 text-center text-white bg-blue-600 rounded-lg shadow-md">
        <h1 class="mb-2 text-2xl font-bold">Votre Position</h1>
        <p class="text-blue-200">Restez informé de votre progression dans la file</p>
    </div>

    @if ($ticket->status === 'called')
        <div class="p-4 text-center text-blue-700 bg-blue-100 border border-blue-400 rounded-lg shadow-md">
            <p class="text-xl font-semibold">Votre ticket <span class="font-bold">{{ $ticket->code_ticket }}</span> est appelé !</p>
            <p class="text-lg">Veuillez rejoindre le guichet.</p>
        </div>
    @elseif ($ticket->status === 'paused')
        <div class="p-4 text-center text-yellow-700 bg-yellow-100 border border-yellow-400 rounded-lg shadow-md">
            <p class="font-semibold">Votre ticket est en pause. Votre position est conservée.</p>
            <p class="text-sm">Vous pouvez revenir dans la file à tout moment.</p>
        </div>
    @elseif ($position <= 3 && $position > 0)
        <div class="p-4 text-center text-orange-700 bg-orange-100 border border-orange-400 rounded-lg shadow-md">
            <p class="font-semibold">Attention ! Votre position est proche. Préparez-vous !</p>
        </div>
    @endif

    @if (!in_array($ticket->status, ['served', 'skipped']))
        <!-- Position Cards Grid -->
        <div class="grid grid-cols-2 gap-4">
            <div class="position-card">
                <div class="position-card-title">Votre Numéro</div>
                <div class="position-card-value">{{ $ticket->code_ticket }}</div>
            </div>
            <div class="position-card">
                <div class="position-card-title">Position Actuelle</div>
                <div class="position-card-value">
                    @if ($ticket->status === 'paused')
                        En pause
                    @else
                        {{ $position }}
                    @endif
                </div>
            </div>
            <div class="position-card">
                <div class="position-card-title">Temps Estimé</div>
                <div class="position-card-value">{{ $estimatedWaitTime }}</div>
            </div>
            <div class="position-card">
                <div class="position-card-title">Statut de la File</div>
                <div class="position-card-value">
                    @php
                        $statusClasses = [
                            'open' => 'bg-green-100 text-green-800',
                            'paused' => 'bg-yellow-100 text-yellow-800',
                            'blocked' => 'bg-red-100 text-red-800',
                            'closed' => 'bg-gray-100 text-gray-800',
                        ][$queue->status->value];
                        
                        $statusLabels = [
                            'open' => 'Active',
                            'paused' => 'En pause',
                            'blocked' => 'Bloquée',
                            'closed' => 'Fermée',
                        ][$queue->status->value];
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full {{ $statusClasses }}">
                        {{ $statusLabels }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Establishment Info Card -->
        <div class="establishment-info-card">
            <div class="flex items-center mb-4">
                {{-- location svg --}}
                <h2 class="text-xl font-bold text-gray-800">Informations établissement</h2>
            </div>
            <p class="mb-2"><span class="font-semibold">Établissement :</span> {{ $queue->establishment->name }}</p>
            {{-- <p class="mb-2"><span class="font-semibold">Type :</span> {{ $queue->name }}</p> --}}
            <p class="mb-2"><span class="font-semibold">Numéro en cours :</span> {{ $currentServingTicketCode }}</p>
            <p><span class="font-semibold">Utilisateurs en attente :</span> {{ $waitingTicketsCount }}</p>
        </div>
    @endif

    @if ($ticket->status === 'served' || $ticket->status === 'skipped')
        <div class="p-4 text-center text-gray-700 bg-gray-100 border border-gray-400 rounded-lg shadow-md space-y-4">
            @if ($ticket->status === 'served')
                <p class="text-xl font-semibold">Votre ticket <span class="font-bold">{{ $ticket->code_ticket }}</span> a été traité.</p>
                <p class="text-base">Nous espérons que vous avez été bien servi. Merci de votre patience !</p>
            @else
                <p class="text-xl font-semibold">Votre ticket <span class="font-bold">{{ $ticket->code_ticket }}</span> a été marqué comme absent.</p>
                <p class="text-base">Veuillez prendre un nouveau ticket si vous souhaitez rejoindre la file à nouveau. Merci de votre compréhension.</p>
            @endif
            <div class="flex justify-center p-4">
                <form action="{{ route('public.queues.index') }}" method="GET" class="w-full max-w-sm">
                    <button type="submit" class="w-full px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                        Quitter
                    </button>
                </form>
            </div>
        </div>
    @else
        {{-- Boutons d'action --}}
        <div class="flex justify-center px-4">
            @if ($ticket->status === 'paused')
                <form wire:submit.prevent="resumeTicket" class="w-full max-w-sm">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                        Retour dans la file
                    </button>
                </form>
            @else
                <form wire:submit.prevent="pauseTicket" class="w-full max-w-sm" onsubmit="return confirm('Êtes-vous sûr de vouloir quitter la file momentanément ? Vous pourrez y revenir plus tard.');">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 text-black bg-yellow-600 rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-opacity-50">
                        Sortie momentanée
                    </button>
                </form>
            @endif
        </div>

        {{-- Bouton annuler --}}
        <div class="flex justify-center px-4">
            <form wire:submit.prevent="cancelTicket" class="w-full max-w-sm" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler votre ticket ? Cette action est irréversible.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full px-4 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50">
                    Quitter
                </button>
            </form>
        </div>
    @endif
</div>
