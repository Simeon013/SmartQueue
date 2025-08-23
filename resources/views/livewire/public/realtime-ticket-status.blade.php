<div wire:poll.2s class="min-h-screen bg-gray-50">
    <!-- Conteneur principal -->
    <div class="mx-auto w-full max-w-4xl">
        <!-- En-tête de la page -->
        {{-- <div class="mb-6 text-center">
            <h1 class="mb-2 text-3xl font-bold text-gray-900">Statut de votre ticket</h1>
            <p class="text-lg text-gray-600">Suivez en temps réel l'avancement de votre file d'attente</p>
        </div> --}}

        @if(!$ticket)
            <!-- Aucun ticket trouvé -->
            <div class="overflow-hidden bg-white rounded-xl border border-red-200 shadow-lg">
                <div class="p-8 text-center">
                    <div class="flex justify-center items-center mx-auto mb-6 w-20 h-20 bg-red-50 rounded-full">
                        <i class="text-4xl text-red-500 fas fa-ticket-alt"></i>
                    </div>

                    <h2 class="mb-3 text-2xl font-bold text-gray-900">Ticket non trouvé</h2>
                    <p class="mb-8 text-lg text-gray-600">Il semble que votre ticket soit expiré ou n'existe pas.</p>

                    <div class="flex flex-col justify-center space-y-4 sm:flex-row sm:space-y-0 sm:space-x-4">
                        @if($queue ?? false)
                            <a href="{{ route('public.queue.show.code', $queue->code) }}"
                               class="inline-flex justify-center items-center px-6 py-3 text-base font-medium text-white bg-blue-600 rounded-md border border-transparent shadow-sm transition-colors duration-200 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="mr-2 fas fa-plus-circle"></i> Prendre un nouveau ticket
                            </a>
                        @endif

                        <a href="{{ route('public.queues.index') }}"
                           class="inline-flex justify-center items-center px-6 py-3 text-base font-medium text-gray-700 bg-white rounded-md border border-gray-300 shadow-sm transition-colors duration-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="mr-2 fas fa-home"></i> Retour à l'accueil
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

            <!-- Notification d'alerte -->
            @if($showNotificationAlert && $latestNotification)
                @php
                    $notificationType = $latestNotification['type'] ?? 'info';
                    $notificationIcons = [
                        'warning' => 'exclamation-triangle',
                        'success' => 'check-circle',
                        'error' => 'times-circle',
                        'info' => 'info-circle'
                    ];
                    $icon = $notificationIcons[$notificationType] ?? 'bell';
                @endphp

                <div class="notification-alert notification-{{ $notificationType }}">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 pt-0.5">
                            <i class="fas fa-{{ $icon }} text-lg"></i>
                        </div>
                        <div class="flex-1 ml-3">
                            <p class="text-sm font-medium">
                                {{ $latestNotification['message'] }}
                            </p>
                            @if(isset($latestNotification['data']['position']))
                                <div class="flex items-center mt-1.5 text-xs opacity-90">
                                    <i class="mr-1.5 fas fa-arrow-up"></i>
                                    <span>Votre position actuelle: <span class="font-semibold">#{{ $latestNotification['data']['position'] }}</span></span>
                                </div>
                            @endif
                            @if(isset($latestNotification['data']['queue_name']))
                                <div class="flex items-center mt-1.5 text-xs opacity-90">
                                    <i class="mr-1.5 fas fa-list-ol"></i>
                                    <span>File: <span class="font-semibold">{{ $latestNotification['data']['queue_name'] }}</span></span>
                                </div>
                            @endif
                        </div>
                        <button
                            wire:click="dismissNotification"
                            class="notification-close"
                            aria-label="Fermer la notification"
                        >
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            @endif

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
                    <div class="flex flex-col items-center mb-6 text-center">
                        <div class="text-6xl font-bold mb-3 {{ $statusInfo['textColor'] }}">{{ $ticket->code_ticket }}</div>
                        <p class="text-xl {{ $statusInfo['textColor'] }} font-medium">
                            {{ $statusInfo['message'] }}
                        </p>
                    </div>

                    <!-- Informations de position -->
                    @if (!in_array($ticket->status, ['served', 'skipped', 'cancelled']))
                        <div class="grid grid-cols-2 gap-3 mb-6">
                            <!-- Votre numéro -->
                            {{-- <div class="p-4 text-center bg-gray-50 rounded-xl border border-gray-200">
                                <div class="mb-2 text-sm font-medium text-gray-500">
                                    <i class="mr-1 fas fa-ticket-alt"></i> Votre numéro
                                </div>
                                <div class="text-2xl font-bold text-gray-900">{{ $ticket->code_ticket }}</div>
                            </div> --}}

                            <!-- Position dans la file -->
                            <div class="p-4 text-center bg-gray-50 rounded-xl border border-gray-200">
                                <div class="mb-2 text-sm font-medium text-gray-500">
                                    <i class="mr-1 fas fa-list-ol"></i> Position
                                </div>
                                <div class="text-2xl font-bold text-gray-900">
                                    @if($position > 0)
                                        {{ $position }}<span class="text-sm font-normal text-gray-500">/{{ $waitingTicketsCount }}</span>
                                    @else
                                        <span class="text-xl text-green-600">C'est à vous !</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Temps d'attente estimé -->
                            <div class="p-4 text-center bg-gray-50 rounded-xl border border-gray-200">
                                <div class="mb-2 text-sm font-medium text-gray-500">
                                    <i class="mr-1 far fa-clock"></i> Temps d'attente
                                </div>
                                <div class="text-2xl font-bold text-gray-900">{{ $estimatedWaitTime ?? '--:--' }}</div>
                            </div>

                            <!-- Tickets en attente -->
                            {{-- <div class="p-4 text-center bg-gray-50 rounded-xl border border-gray-200">
                                <div class="mb-2 text-sm font-medium text-gray-500">
                                    <i class="mr-1 fas fa-users"></i> En attente
                                </div>
                                <div class="text-2xl font-bold text-gray-900">{{ $waitingTicketsCount ?? '0' }}</div>
                            </div> --}}
                        </div>

                        <!-- Messages d'état spéciaux -->
                        @if ($ticket->status === 'in_progress')
                            <div class="p-4 mb-6 bg-yellow-50 rounded-r-lg border-l-4 border-yellow-500">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <i class="mt-0.5 text-xl text-yellow-500 fas fa-exclamation-triangle"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-base font-medium text-yellow-800">
                                            <strong>À votre tour !</strong> Veuillez vous présenter au comptoir.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Messages d'état spéciaux -->
                        @if ($ticket->status === 'paused')
                            <div class="p-4 mb-6 bg-blue-50 rounded-r-lg border-l-4 border-blue-500">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <i class="mt-0.5 text-xl text-blue-500 fas fa-info-circle"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-base font-medium text-blue-800">
                                            Votre ticket reste dans la file d'attente. Vous serez notifié lorsque votre tour approchera.
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
                                    class="flex justify-center items-center px-6 py-4 w-full text-lg font-semibold text-yellow-800 bg-yellow-50 rounded-xl border-2 border-yellow-400 shadow-sm transition-colors duration-200 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                                >
                                    <i class="mr-2 fas fa-sign-out-alt"></i>
                                    Sortie momentanée
                                </button>

                                <button
                                    wire:click="$set('showCancelModal', true)"
                                    class="flex justify-center items-center px-6 py-4 w-full text-lg font-semibold text-gray-800 bg-white rounded-xl border-2 border-gray-300 shadow-sm transition-colors duration-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                >
                                    <i class="mr-3 text-xl fas fa-times-circle"></i>
                                    Annuler mon ticket
                                </button>

                            @elseif($ticket->status === 'paused')
                                <button
                                    wire:click="resumeTicket"
                                    class="flex justify-center items-center px-6 py-4 w-full text-lg font-semibold text-green-800 bg-green-50 rounded-xl border-2 border-green-500 shadow-sm transition-colors duration-200 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                >
                                    <i class="mr-3 text-xl fas fa-play"></i>
                                    Reprendre ma place
                                </button>

                                <button
                                    wire:click="$set('showCancelModal', true)"
                                    class="flex justify-center items-center px-6 py-4 w-full text-lg font-semibold text-gray-800 bg-white rounded-xl border-2 border-gray-300 shadow-sm transition-colors duration-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                >
                                    <i class="mr-3 text-xl fas fa-times-circle"></i>
                                    Annuler mon ticket
                                </button>
                            @endif
                        </div>
                    @elseif($ticket->status === 'cancelled')
                        <div class="p-4 mb-6 bg-red-50 rounded-r-lg border-l-4 border-red-500">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <i class="mt-0.5 text-xl text-red-500 fas fa-exclamation-circle"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-base font-medium text-red-800">
                                        <strong>Ticket annulé</strong> Ce ticket n'est plus valide. Veuillez en prendre un nouveau si nécessaire.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @elseif(in_array($ticket->status, ['served', 'skipped']))
                        <div class="p-4 mb-6 bg-gray-50 rounded-r-lg border-l-4 border-gray-500">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <i class="mt-0.5 text-xl text-gray-500 fas fa-info-circle"></i>
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
                        <div class="overflow-hidden mt-8 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border-2 border-blue-100 shadow-md">
                            <div class="p-5 sm:p-6">
                                <h3 class="flex items-center mb-3 text-xl font-bold text-gray-900">
                                    <i class="mr-3 text-2xl text-yellow-400 fas fa-star"></i>
                                    Donnez votre avis
                                </h3>
                                <p class="mb-6 text-base text-gray-600">Aidez-nous à améliorer notre service en partageant votre expérience.</p>

                                @if($reviewSubmitted)
                                    <div class="flex items-start p-4 bg-green-50 rounded-lg border-l-4 border-green-500">
                                        <i class="mt-0.5 mr-3 text-xl text-green-500 fas fa-check-circle"></i>
                                        <p class="font-medium text-green-800">Merci pour votre avis !</p>
                                    </div>
                                @else
                                    <form wire:submit.prevent="submitReview">
                                        <div class="mb-6">
                                            <label for="rating" class="block mb-3 text-base font-medium text-gray-700">
                                                Note
                                            </label>
                                            <div class="flex justify-center items-center mb-2 space-x-1 sm:space-x-2">
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
                                            <div class="text-sm text-center text-gray-500">
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
                                                <p class="mt-2 text-sm text-center text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="mb-6">
                                            <label for="comment" class="block mb-2 text-base font-medium text-gray-700">
                                                Votre avis (optionnel)
                                            </label>
                                            <textarea
                                                id="comment"
                                                wire:model="comment"
                                                rows="4"
                                                class="px-4 py-3 w-full text-base rounded-xl border-2 border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                placeholder="Dites-nous ce que vous avez pensé de notre service..."
                                            ></textarea>
                                            @error('comment')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="flex justify-end">
                                            <button
                                                type="submit"
                                                class="flex justify-center items-center px-6 py-3 w-full text-base font-medium text-white bg-blue-600 rounded-xl border border-transparent shadow-sm transition-colors duration-200 sm:w-auto hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                            >
                                                <i class="mr-2 fas fa-paper-plane"></i>
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
                                class="block px-6 py-4 w-full text-lg font-semibold text-center text-white bg-blue-600 rounded-xl border-2 border-transparent shadow-sm transition-colors duration-200 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                <i class="mr-3 text-xl fas fa-home"></i>
                                Retour à l'accueil
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Section d'information sur la file d'attente -->
            @if(isset($queue))
                <div class="overflow-hidden mt-8 bg-white rounded-xl shadow-md">
                    <div class="p-6">
                        <h2 class="flex items-center mb-4 text-xl font-bold text-gray-900">
                            <i class="mr-2 text-blue-500 fas fa-info-circle"></i>
                            Informations sur la file d'attente
                        </h2>

                        <div class="space-y-4">
                            <div class="flex flex-col justify-between py-3 border-b border-gray-100 sm:flex-row">
                                <span class="flex items-center text-gray-600">
                                    <i class="mr-2 w-5 text-center text-blue-400 fas fa-signature"></i>
                                    Nom de la file
                                </span>
                                <span class="mt-1 font-medium text-gray-900 sm:mt-0">{{ $queue->name }}</span>
                            </div>

                            <div class="flex flex-col justify-between py-3 border-b border-gray-100 sm:flex-row">
                                <span class="flex items-center text-gray-600">
                                    <i class="mr-2 w-5 text-center text-blue-400 fas fa-building"></i>
                                    Établissement
                                </span>
                                <span class="mt-1 font-medium text-gray-900 sm:mt-0">{{ $queue->establishment->name }}</span>
                            </div>

                            @if($queue->description)
                                <div class="py-3 border-b border-gray-100">
                                    <p class="flex items-center mb-1 text-gray-600">
                                        <i class="mr-2 w-5 text-center text-blue-400 fas fa-align-left"></i>
                                        Description
                                    </p>
                                    <p class="mt-1 text-gray-700">{{ $queue->description }}</p>
                                </div>
                            @endif

                            <div class="flex flex-col justify-between py-3 border-b border-gray-100 sm:flex-row">
                                <span class="flex items-center text-gray-600">
                                    <i class="mr-2 w-5 text-center text-blue-400 fas fa-users"></i>
                                    Personnes devant vous
                                </span>
                                <span class="font-medium text-gray-900">{{ $waitingTicketsCount ?? '0' }} personne(s)</span>
                            </div>

                            @if(isset($currentServingTicketCode) && $currentServingTicketCode !== 'Aucun ticket en cours de traitement')
                                <div class="flex items-start p-4 mt-4 bg-blue-50 rounded-lg">
                                    <i class="mt-1 mr-3 text-blue-500 fas fa-bullhorn"></i>
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
                <div class="overflow-y-auto fixed inset-0 z-10">
                    <div class="flex justify-center items-end px-4 pt-4 pb-20 min-h-screen text-center sm:block sm:p-0">
                        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                        </div>
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                        <div class="inline-block overflow-hidden px-4 pt-5 pb-4 text-left align-bottom bg-white rounded-lg shadow-xl transition-all transform sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                            <div class="sm:flex sm:items-start">
                                <div class="flex flex-shrink-0 justify-center items-center mx-auto w-12 h-12 bg-red-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                                    <i class="text-red-600 fas fa-exclamation"></i>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">Annuler le ticket</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">Êtes-vous sûr de vouloir annuler votre ticket ? Cette action est irréversible.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                <button wire:click="cancelTicket" type="button" class="inline-flex justify-center px-4 py-2 w-full text-base font-medium text-white bg-red-600 rounded-md border border-transparent shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                    Oui, annuler
                                </button>
                                <button wire:click="$set('showCancelModal', false)" type="button" class="inline-flex justify-center px-4 py-2 mt-3 w-full text-base font-medium text-gray-700 bg-white rounded-md border border-gray-300 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
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
