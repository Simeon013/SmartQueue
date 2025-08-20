<div wire:poll.2s class="space-y-4">
    @if(!$ticket)
        @php
            // Utiliser le statut du ticket s'il existe, sinon utiliser un statut par défaut
            $ticketStatus = 'not_found';
            // dd($ticketStatus);

            // Définir les messages et couleurs par défaut
            $message = 'Ticket non trouvé ou expiré';
            $description = 'Veuillez prendre un nouveau ticket si nécessaire.';
            $bgColor = 'red-100';
            $textColor = 'red-700';
            $borderColor = 'red-400';
            $showNewTicketBtn = true;
            $showReturnBtn = false;
            $icon = 'exclamation-circle';
        @endphp

        <div class="p-8 text-center bg-white rounded-lg shadow-lg border border-{{ $borderColor }}">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-{{ $bgColor }} mb-4">
                <i class="fas fa-{{ $icon }} text-3xl text-{{ $textColor }}"></i>
            </div>

            <h3 class="text-2xl font-bold text-{{ $textColor }} mb-2">{{ $message }}</h3>
            <p class="mb-6 text-lg text-gray-600">{{ $description }}</p>

            <div class="flex flex-col gap-4 justify-center sm:flex-row">
                <a href="{{ route('public.queue.show.code', $queue->code) }}"
                   class="inline-flex justify-center items-center px-6 py-3 text-base font-medium text-white bg-blue-600 rounded-md border border-transparent hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="mr-2 fas fa-plus-circle"></i> Prendre un nouveau ticket
                </a>

                <a href="{{ route('public.queues.index') }}"
                   class="inline-flex justify-center items-center px-6 py-3 text-base font-medium text-gray-700 bg-white rounded-md border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="mr-2 fas fa-home"></i> Retour à l'accueil
                </a>
            </div>
        </div>
    @else
        @php
            // Utiliser le statut du ticket
            $ticketStatus = $ticket->status;
            // dd($ticketStatus);

            // Définir les messages et couleurs par défaut
            $message = 'Statut inconnu';
            $description = 'Veuillez contacter le support si nécessaire.';
            $bgColor = 'gray-100';
            $textColor = 'gray-800';
            $borderColor = 'gray-200';
            $showNewTicketBtn = true;
            $showReturnBtn = true;
            $icon = 'info-circle';

            // Personnaliser le message en fonction du statut du ticket
            switch($ticketStatus) {
                case 'served':
                    $message = '🎉 Félicitations ! Votre ticket a été traité avec succès !';
                    $description = 'Merci d\'avoir utilisé notre service. Nous espérons vous revoir bientôt !';
                    $bgColor = 'green-50';
                    $textColor = 'green-800';
                    $borderColor = 'green-200';
                    $icon = 'check-circle';
                    $showNewTicketBtn = false;
                    break;

                case 'skipped':
                    $message = '⏱️ Votre ticket a été marqué comme absent';
                    $description = 'Vous n\'étiez pas présent(e) lors de l\'appel de votre numéro. Si vous souhaitez rejoindre à nouveau la file, veuillez prendre un nouveau ticket.';
                    $bgColor = 'yellow-50';
                    $textColor = 'yellow-800';
                    $borderColor = 'yellow-200';
                    $icon = 'user-clock';
                    break;

                case 'cancelled':
                    $message = '❌ Votre ticket a été annulé';
                    $description = 'Si vous souhaitez rejoindre à nouveau la file, veuillez prendre un nouveau ticket.';
                    $bgColor = 'gray-50';
                    $textColor = 'gray-800';
                    $borderColor = 'gray-200';
                    $icon = 'times-circle';
                    break;

                case 'waiting':
                case 'in_progress':
                case 'paused':
                    // Ces cas sont gérés dans la vue principale
                    break;

                default:
                    $message = 'ℹ️ ' . $message;
                    $icon = 'info-circle';
            }
        @endphp

        @if(in_array($ticketStatus, ['waiting', 'in_progress', 'paused']))
            <!-- Afficher les informations du ticket en cours -->
            <div class="p-6 bg-white rounded-lg shadow">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold text-gray-900">Votre ticket</h3>
                    <span class="px-3 py-1 text-sm font-semibold text-white bg-blue-600 rounded-full">
                        {{ strtoupper($ticketStatus) }}
                    </span>
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Numéro de ticket</span>
                        <span class="font-medium">{{ $ticket->code_ticket }}</span>
                    </div>

                    @if($ticket->position)
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Position dans la file</span>
                        <span class="font-medium">#{{ $ticket->position }}</span>
                    </div>
                    @endif

                    @if($estimatedWaitTime !== '--:--')
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Temps d'attente estimé</span>
                        <span class="font-medium">{{ $estimatedWaitTime }}</span>
                    </div>
                    @endif

                    @if($ticketStatus === 'paused')
                    <div class="pt-4 mt-4 border-t border-gray-200">
                        <button wire:click="resumeTicket" class="px-4 py-2 w-full text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="mr-2 fas fa-play"></i> Reprendre mon ticket
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        @else
            <!-- Afficher le message de statut -->
            <div class="p-8 text-center bg-white rounded-lg shadow-lg border border-{{ $borderColor }}">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-{{ $bgColor }} mb-4">
                    <i class="fas fa-{{ $icon }} text-3xl text-{{ $textColor }}"></i>
                </div>

                <h3 class="text-2xl font-bold text-{{ $textColor }} mb-2">{{ $message }}</h3>
                <p class="mb-6 text-lg text-gray-600">{{ $description }}</p>

                @if($showReviewForm)
                    <!-- Formulaire d'avis -->
                    <div class="mt-8 p-6 bg-gray-50 rounded-lg border border-gray-200">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Donnez votre avis sur notre service</h4>
                        
                        @if(session('review_submitted'))
                            <div class="mb-4 p-4 bg-green-50 text-green-700 rounded-md">
                                {{ session('review_submitted') }}
                            </div>
                        @elseif(session('error'))
                            <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-md">
                                {{ session('error') }}
                            </div>
                        @else
                            <form wire:submit.prevent="submitReview">
                                <!-- Note en étoiles -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Note
                                    </label>
                                    <div class="flex items-center space-x-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <button type="button" 
                                                    wire:click="$set('rating', {{ $i }})" 
                                                    class="text-3xl {{ $i <= $rating ? 'text-yellow-500' : 'text-gray-300' }}">
                                                ★
                                            </button>
                                        @endfor
                                    </div>
                                    @error('rating') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                
                                <!-- Commentaire -->
                                <div class="mb-4">
                                    <label for="comment" class="block text-sm font-medium text-gray-700 mb-1">
                                        Commentaire (optionnel)
                                    </label>
                                    <textarea 
                                        id="comment"
                                        wire:model="comment"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Dites-nous ce que vous avez pensé de notre service..."></textarea>
                                    @error('comment') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                
                                <!-- Bouton de soumission -->
                                <div class="flex justify-end">
                                    <button 
                                        type="submit"
                                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Envoyer mon avis
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                @endif
                
                <div class="flex flex-col gap-4 justify-center sm:flex-row mt-6">
                    @if($showNewTicketBtn)
                        <a href="{{ route('public.queue.show.code', $queue->code) }}"
                           class="inline-flex justify-center items-center px-6 py-3 text-base font-medium text-white bg-blue-600 rounded-md border border-transparent hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="mr-2 fas fa-plus-circle"></i> Prendre un nouveau ticket
                        </a>
                    @endif

                    <a href="{{ route('public.queues.index') }}"
                       class="inline-flex justify-center items-center px-6 py-3 text-base font-medium text-gray-700 bg-white rounded-md border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="mr-2 fas fa-home"></i> Retour à l'accueil
                    </a>
                </div>
            </div>
        @endif
        <!-- Position Banner -->
        <div class="p-6 text-center text-white bg-blue-600 rounded-lg shadow-md">
            <h1 class="mb-2 text-2xl font-bold">Votre Position</h1>
            <p class="text-blue-200">Restez informé de votre progression dans la file</p>
        </div>

        @if ($ticket->status === 'cancelled')
            <div class="p-4 text-center text-red-700 bg-red-100 rounded-lg border border-red-400 shadow-md">
                <p class="text-xl font-semibold">Votre ticket <span class="font-bold">{{ $ticket->code_ticket }}</span> a été annulé.</p>
                <p class="text-lg">Si vous souhaitez rejoindre à nouveau la file, veuillez prendre un nouveau ticket.</p>
            </div>
        @elseif ($ticket->status === 'in_progress')
            <div class="p-4 text-center text-blue-700 bg-blue-100 rounded-lg border border-blue-400 shadow-md">
                <p class="text-xl font-semibold">Votre ticket <span class="font-bold">{{ $ticket->code_ticket }}</span> est en cours de traitement !</p>
                @if($ticket->handler)
                    <p class="text-lg">Veuillez rejoindre l'agent.</p>
                @else
                    <p class="text-lg">Veuillez vous présenter à l'accueil.</p>
                @endif
            </div>
        @elseif ($ticket->status === 'paused')
            <div class="p-4 text-center text-yellow-700 bg-yellow-100 rounded-lg border border-yellow-400 shadow-md">
                <p class="font-semibold">Votre ticket est en pause. Votre position est conservée.</p>
                <p class="text-sm">Vous pouvez revenir dans la file à tout moment.</p>
            </div>
        @elseif ($position <= 3 && $position > 0)
            <div class="p-4 text-center text-orange-700 bg-orange-100 rounded-lg border border-orange-400 shadow-md">
                <p class="font-semibold">Attention ! Votre position est proche. Préparez-vous !</p>
            </div>
        @endif

        @if (!in_array($ticket->status, ['served', 'skipped', 'cancelled']))
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
                @if($ticket->status !== 'paused')
                <div class="position-card">
                    <div class="position-card-title">Temps d'attente estimé</div>
                    <div class="position-card-value">
                        {{ $estimatedWaitTime }}
                    </div>
                </div>
                @endif
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
                    <h2 class="text-xl font-bold text-gray-800">Informations établissement</h2>
                </div>
                <p class="mb-2"><span class="font-semibold">Établissement :</span> {{ $queue->establishment->name }}</p>
                @if($ticket->status !== 'paused')
                    <p class="mb-2"><span class="font-semibold">Numéro en cours :</span> {{ $currentServingTicketCode }}</p>
                    <p><span class="font-semibold">Utilisateurs en attente :</span> {{ $waitingTicketsCount }}</p>
                @endif
            </div>
        @endif

        @if ($ticket->status === 'served' || $ticket->status === 'skipped')
            <div class="p-4 space-y-4 text-center text-gray-700 bg-gray-100 rounded-lg border border-gray-400 shadow-md">
                @if ($ticket->status === 'served')
                    <p class="text-xl font-semibold">Votre ticket <span class="font-bold">{{ $ticket->code_ticket }}</span> a été traité.</p>
                    <p class="text-base">Nous espérons que vous avez été bien servi. Merci de votre patience !</p>
                @else
                    <p class="text-xl font-semibold">Votre ticket <span class="font-bold">{{ $ticket->code_ticket }}</span> a été marqué comme absent.</p>
                    <p class="text-base">Veuillez prendre un nouveau ticket si vous souhaitez rejoindre la file à nouveau. Merci de votre compréhension.</p>
                @endif
                <div class="flex justify-center p-4">
                    <form action="{{ route('public.queues.index') }}" method="GET" class="w-full max-w-sm">
                        <button type="submit" class="px-4 py-2 w-full text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                            Quitter
                        </button>
                    </form>
                </div>
            </div>
        @elseif ($ticket->status === 'cancelled')
            <div class="p-6 text-center">
                <a href="{{ route('public.queue.show.code', $queue->code) }}" class="px-6 py-3 font-medium text-white bg-blue-600 rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="mr-2 fas fa-plus-circle"></i> Prendre un nouveau ticket
                </a>
            </div>
        @elseif (!in_array($ticket->status, ['served', 'skipped', 'cancelled']))
            {{-- Boutons d'action --}}
            <div class="flex justify-center px-4">
                @if ($ticket->status === 'paused')
                    <form wire:submit.prevent="resumeTicket" class="w-full max-w-sm">
                        @csrf
                        <button type="submit" class="px-4 py-2 w-full text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                            Retour dans la file
                        </button>
                    </form>
                @else
                    <form wire:submit.prevent="pauseTicket" class="w-full max-w-sm" onsubmit="return confirm('Êtes-vous sûr de vouloir quitter la file momentanément ? Vous pourrez y revenir plus tard.');">
                        @csrf
                        <button type="submit" class="px-4 py-2 w-full text-black bg-yellow-600 rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-opacity-50">
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
                    <button type="submit" class="px-4 py-2 w-full text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50">
                        Quitter
                    </button>
                </form>
            </div>
        @endif
    @endif
</div>
