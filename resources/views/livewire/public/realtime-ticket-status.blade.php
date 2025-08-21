<div wire:poll.2s class="min-h-screen bg-gray-50">
    <!-- Conteneur principal -->
    <div class="w-full max-w-4xl mx-auto">
        <!-- En-tête de la page -->
        {{-- <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Statut de votre ticket</h1>
            <p class="text-lg text-gray-600">Suivez en temps réel l'avancement de votre file d'attente</p>
        </div> --}}

        @if(!$ticket)
            <!-- Aucun ticket trouvé -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-red-200">
                <div class="p-8 text-center">
                    <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-50 mb-6">
                        <i class="fas fa-ticket-alt text-4xl text-red-500"></i>
                    </div>

                    <h2 class="text-2xl font-bold text-gray-900 mb-3">Ticket non trouvé</h2>
                    <p class="text-lg text-gray-600 mb-8">Il semble que votre ticket soit expiré ou n'existe pas.</p>

                    <div class="flex flex-col space-y-4 sm:flex-row sm:space-y-0 sm:space-x-4 justify-center">
                        @if($queue ?? false)
                            <a href="{{ route('public.queue.show.code', $queue->code) }}"
                               class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                <i class="fas fa-plus-circle mr-2"></i> Prendre un nouveau ticket
                            </a>
                        @endif

                        <a href="{{ route('public.queues.index') }}"
                           class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            <i class="fas fa-home mr-2"></i> Retour à l'accueil
                        </a>
                    </div>
                </div>
            </div>
        @else
            @php
                $statusInfo = [
                    'waiting' => [
                        'bgColor' => 'bg-blue-50',
                        'textColor' => 'text-blue-800',
                        'borderColor' => 'border-blue-200',
                        'icon' => 'clock',
                        'message' => 'En attente de traitement',
                        'class' => 'bg-blue-100 text-blue-800',
                        'label' => 'En attente',
                        'emoji' => '⏳',
                        'color' => 'blue'
                    ],
                    'in_progress' => [
                        'bgColor' => 'bg-yellow-50',
                        'textColor' => 'text-yellow-800',
                        'borderColor' => 'border-yellow-200',
                        'icon' => 'user-clock',
                        'message' => 'Présentez-vous au comptoir',
                        'class' => 'bg-yellow-100 text-yellow-800',
                        'label' => 'À vous de jouer',
                        'emoji' => '🚨',
                        'color' => 'yellow'
                    ],
                    'paused' => [
                        'bgColor' => 'bg-orange-50',
                        'textColor' => 'text-orange-800',
                        'borderColor' => 'border-orange-200',
                        'icon' => 'pause-circle',
                        'message' => 'Votre place est en pause',
                        'class' => 'bg-orange-100 text-orange-800',
                        'label' => 'En pause',
                        'emoji' => '⏸️',
                        'color' => 'orange'
                    ],
                    'served' => [
                        'bgColor' => 'bg-green-50',
                        'textColor' => 'text-green-800',
                        'borderColor' => 'border-green-200',
                        'icon' => 'check-circle',
                        'message' => 'Service terminé avec succès',
                        'class' => 'bg-green-100 text-green-800',
                        'label' => 'Terminé',
                        'emoji' => '✅',
                        'color' => 'green'
                    ],
                    'cancelled' => [
                        'bgColor' => 'bg-red-50',
                        'textColor' => 'text-red-800',
                        'borderColor' => 'border-red-200',
                        'icon' => 'times-circle',
                        'message' => 'Ce ticket a été annulé',
                        'class' => 'bg-red-100 text-red-800',
                        'label' => 'Annulé',
                        'emoji' => '❌',
                        'color' => 'red'
                    ],
                    'skipped' => [
                        'bgColor' => 'bg-gray-50',
                        'textColor' => 'text-gray-800',
                        'borderColor' => 'border-gray-200',
                        'icon' => 'forward',
                        'message' => 'Vous avez été marqué comme absent',
                        'class' => 'bg-gray-100 text-gray-800',
                        'label' => 'Passé',
                        'emoji' => '⏭️',
                        'color' => 'gray'
                    ]
                ][$ticket->status] ?? [
                    'bgColor' => 'bg-gray-50',
                    'textColor' => 'text-gray-800',
                    'borderColor' => 'border-gray-200',
                    'icon' => 'question-circle',
                    'message' => 'Statut inconnu',
                    'class' => 'bg-gray-100 text-gray-800',
                    'label' => 'Inconnu',
                    'emoji' => '❓',
                    'color' => 'gray'
                ];
            @endphp

            <!-- Carte principale du statut -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border-2 {{ $statusInfo['borderColor'] }} mb-6 transition-all duration-300 hover:shadow-xl">
                <!-- Bandeau de statut -->
                <div class="px-4 py-3 sm:px-6 {{ $statusInfo['bgColor'] }} {{ $statusInfo['textColor'] }} flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas {{ $statusInfo['icon'] }} text-xl mr-3"></i>
                        <span class="text-lg font-semibold">{{ $statusInfo['label'] }}</span>
                    </div>
                    <span class="text-2xl">{{ $statusInfo['emoji'] }}</span>
                </div>

                <!-- Contenu principal -->
                <div class="p-4 sm:p-6">
                    <div class="flex flex-col items-center text-center mb-6">
                        <div class="text-6xl font-bold mb-3 {{ $statusInfo['textColor'] }}">{{ $ticket->code_ticket }}</div>
                        <p class="text-xl {{ $statusInfo['textColor'] }} font-medium">
                            {{ $statusInfo['message'] }}
                        </p>
                    </div>

                    <!-- Informations de position -->
                    @if (!in_array($ticket->status, ['served', 'skipped', 'cancelled']))
                        <div class="grid grid-cols-2 gap-3 mb-6">
                            <!-- Votre numéro -->
                            {{-- <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 text-center">
                                <div class="text-sm font-medium text-gray-500 mb-2">
                                    <i class="fas fa-ticket-alt mr-1"></i> Votre numéro
                                </div>
                                <div class="text-2xl font-bold text-gray-900">{{ $ticket->code_ticket }}</div>
                            </div> --}}

                            <!-- Position dans la file -->
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 text-center">
                                <div class="text-sm font-medium text-gray-500 mb-2">
                                    <i class="fas fa-list-ol mr-1"></i> Position
                                </div>
                                <div class="text-2xl font-bold text-gray-900">
                                    @if($position > 0)
                                        {{ $position }}<span class="text-sm font-normal text-gray-500">/{{ $waitingTicketsCount }}</span>
                                    @else
                                        <span class="text-green-600 text-xl">C'est à vous !</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Temps d'attente estimé -->
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 text-center">
                                <div class="text-sm font-medium text-gray-500 mb-2">
                                    <i class="far fa-clock mr-1"></i> Temps d'attente
                                </div>
                                <div class="text-2xl font-bold text-gray-900">{{ $estimatedWaitTime ?? '--:--' }}</div>
                            </div>

                            <!-- Tickets en attente -->
                            {{-- <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 text-center">
                                <div class="text-sm font-medium text-gray-500 mb-2">
                                    <i class="fas fa-users mr-1"></i> En attente
                                </div>
                                <div class="text-2xl font-bold text-gray-900">{{ $waitingTicketsCount ?? '0' }}</div>
                            </div> --}}
                        </div>

                        <!-- Messages d'état spéciaux -->
                        @if ($ticket->status === 'in_progress')
                            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6 rounded-r-lg">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-triangle text-yellow-500 text-xl mt-0.5"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-base font-medium text-yellow-800">
                                            <strong>À votre tour !</strong> Veuillez vous présenter au comptoir.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Actions du ticket -->
                        <div class="mt-6 space-y-4">
                            @if($ticket->status === 'waiting')
                                <button
                                    wire:click="pauseTicket"
                                    class="w-full flex items-center justify-center px-6 py-4 border-2 border-yellow-400 text-lg font-semibold rounded-xl shadow-sm text-yellow-800 bg-yellow-50 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors duration-200"
                                >
                                    <i class="fas fa-pause text-xl mr-3"></i>
                                    Mettre en pause ma place
                                </button>

                                <button
                                    wire:click="$set('showCancelModal', true)"
                                    class="w-full flex items-center justify-center px-6 py-4 border-2 border-gray-300 text-lg font-semibold rounded-xl shadow-sm text-gray-800 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200"
                                >
                                    <i class="fas fa-times-circle text-xl mr-3"></i>
                                    Annuler mon ticket
                                </button>

                            @elseif($ticket->status === 'paused')
                                <button
                                    wire:click="resumeTicket"
                                    class="w-full flex items-center justify-center px-6 py-4 border-2 border-green-500 text-lg font-semibold rounded-xl shadow-sm text-green-800 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200"
                                >
                                    <i class="fas fa-play text-xl mr-3"></i>
                                    Reprendre ma place
                                </button>

                                <button
                                    wire:click="$set('showCancelModal', true)"
                                    class="w-full flex items-center justify-center px-6 py-4 border-2 border-gray-300 text-lg font-semibold rounded-xl shadow-sm text-gray-800 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200"
                                >
                                    <i class="fas fa-times-circle text-xl mr-3"></i>
                                    Annuler mon ticket
                                </button>
                            @endif
                        </div>
                    @elseif($ticket->status === 'cancelled')
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-red-500 text-xl mt-0.5"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-base font-medium text-red-800">
                                        <strong>Ticket annulé</strong> Ce ticket n'est plus valide. Veuillez en prendre un nouveau si nécessaire.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @elseif(in_array($ticket->status, ['served', 'skipped']))
                        <div class="bg-gray-50 border-l-4 border-gray-500 p-4 mb-6 rounded-r-lg">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-gray-500 text-xl mt-0.5"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-base font-medium text-gray-800">
                                        @if($ticket->status === 'served')
                                            <strong>Service terminé</strong> Nous espérons que vous avez été satisfait de notre service.
                                        @else
                                            <strong>Absence constatée</strong> Vous avez été marqué comme absent. Prenez un nouveau ticket si nécessaire.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Formulaire d'avis -->
                    @if($ticket->status === 'served' && !$ticket->has_review && !$reviewSubmitted)
                        <div class="mt-8 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl shadow-md overflow-hidden border-2 border-blue-100">
                            <div class="p-5 sm:p-6">
                                <h3 class="text-xl font-bold text-gray-900 mb-3 flex items-center">
                                    <i class="fas fa-star text-yellow-400 text-2xl mr-3"></i>
                                    Donnez votre avis
                                </h3>
                                <p class="text-gray-600 text-base mb-6">Aidez-nous à améliorer notre service en partageant votre expérience.</p>

                                @if($reviewSubmitted)
                                    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg flex items-start">
                                        <i class="fas fa-check-circle text-green-500 text-xl mt-0.5 mr-3"></i>
                                        <p class="text-green-800 font-medium">Merci pour votre avis !</p>
                                    </div>
                                @else
                                    <form wire:submit.prevent="submitReview">
                                        <div class="mb-6">
                                            <label for="rating" class="block text-base font-medium text-gray-700 mb-3">
                                                Note
                                            </label>
                                            <div class="flex items-center justify-center space-x-1 sm:space-x-2 mb-2">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <button
                                                        type="button"
                                                        wire:click="$set('rating', {{ $i }})"
                                                        class="text-4xl focus:outline-none {{ $i <= $rating ? 'text-yellow-400' : 'text-gray-300' }} hover:text-yellow-400 transition-transform transform hover:scale-110"
                                                        aria-label="{{ $i }} étoile(s)"
                                                    >
                                                        ★
                                                    </button>
                                                @endfor
                                            </div>
                                            <div class="text-center text-sm text-gray-500">
                                                @switch($rating)
                                                    @case(1) Très insatisfait @break
                                                    @case(2) Insatisfait @break
                                                    @case(3) Moyen @break
                                                    @case(4) Satisfait @break
                                                    @case(5) Très satisfait @break
                                                    @default Sélectionnez une note
                                                @endswitch
                                            </div>
                                            @error('rating')
                                                <p class="mt-2 text-sm text-red-600 text-center">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="mb-6">
                                            <label for="comment" class="block text-base font-medium text-gray-700 mb-2">
                                                Votre avis (optionnel)
                                            </label>
                                            <textarea
                                                id="comment"
                                                wire:model="comment"
                                                rows="4"
                                                class="w-full px-4 py-3 text-base border-2 border-gray-200 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                placeholder="Dites-nous ce que vous avez pensé de notre service..."
                                            ></textarea>
                                            @error('comment')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="flex justify-end">
                                            <button
                                                type="submit"
                                                class="w-full sm:w-auto flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-xl shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                                            >
                                                <i class="fas fa-paper-plane mr-2"></i>
                                                Envoyer l'avis
                                            </button>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endif
                    
                    <!-- Bouton de retour à l'accueil pour les états terminés -->
                    @if(in_array($ticket->status, ['served', 'skipped', 'cancelled']))
                        <div class="mt-6">
                            <a
                                href="{{ route('public.queues.index') }}"
                                class="block w-full text-center px-6 py-4 border-2 border-transparent text-lg font-semibold rounded-xl shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                            >
                                <i class="fas fa-home text-xl mr-3"></i>
                                Retour à l'accueil
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Section d'information sur la file d'attente -->
            @if(isset($queue))
                <div class="mt-8 bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                            Informations sur la file d'attente
                        </h2>

                        <div class="space-y-4">
                            <div class="flex flex-col sm:flex-row justify-between py-3 border-b border-gray-100">
                                <span class="text-gray-600 flex items-center">
                                    <i class="fas fa-signature text-blue-400 mr-2 w-5 text-center"></i>
                                    Nom de la file
                                </span>
                                <span class="font-medium text-gray-900 mt-1 sm:mt-0">{{ $queue->name }}</span>
                            </div>

                            <div class="flex flex-col sm:flex-row justify-between py-3 border-b border-gray-100">
                                <span class="text-gray-600 flex items-center">
                                    <i class="fas fa-building text-blue-400 mr-2 w-5 text-center"></i>
                                    Établissement
                                </span>
                                <span class="font-medium text-gray-900 mt-1 sm:mt-0">{{ $queue->establishment->name }}</span>
                            </div>

                            @if($queue->description)
                                <div class="py-3 border-b border-gray-100">
                                    <p class="text-gray-600 mb-1 flex items-center">
                                        <i class="fas fa-align-left text-blue-400 mr-2 w-5 text-center"></i>
                                        Description
                                    </p>
                                    <p class="text-gray-700 mt-1">{{ $queue->description }}</p>
                                </div>
                            @endif

                            <div class="flex flex-col sm:flex-row justify-between py-3 border-b border-gray-100">
                                <span class="text-gray-600 flex items-center">
                                    <i class="fas fa-users text-blue-400 mr-2 w-5 text-center"></i>
                                    Personnes devant vous
                                </span>
                                <span class="font-medium text-gray-900">{{ $waitingTicketsCount ?? '0' }} personne(s)</span>
                            </div>

                            @if(isset($currentServingTicketCode) && $currentServingTicketCode !== 'Aucun ticket en cours de traitement')
                                <div class="bg-blue-50 p-4 rounded-lg mt-4 flex items-start">
                                    <i class="fas fa-bullhorn text-blue-500 mt-1 mr-3"></i>
                                    <div>
                                        <p class="text-sm text-blue-700">
                                            <span class="font-medium">Ticket en cours :</span> {{ $currentServingTicketCode }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Modal de confirmation d'annulation -->
            @if(isset($showCancelModal) && $showCancelModal)
                <div class="fixed z-10 inset-0 overflow-y-auto">
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                        </div>
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <i class="fas fa-exclamation text-red-600"></i>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">Annuler le ticket</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">Êtes-vous sûr de vouloir annuler votre ticket ? Cette action est irréversible.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                <button wire:click="cancelTicket" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                    Oui, annuler
                                </button>
                                <button wire:click="$set('showCancelModal', false)" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
                                    Non, garder mon ticket
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
