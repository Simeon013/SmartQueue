@props(['ticket', 'position', 'estimatedWaitTime', 'waitingTicketsCount'])

<div class="grid grid-cols-2 gap-4">
    <!-- Votre numéro -->
    <div class="position-card">
        <div class="position-card-title">Votre Numéro</div>
        <div class="position-card-value">{{ $ticket->code_ticket }}</div>
    </div>

    <!-- Position dans la file -->
    <div class="position-card">
        <div class="position-card-title">Position</div>
        <div class="position-card-value">
            @if($position > 0)
                {{ $position }}<span class="text-sm font-normal">/{{ $waitingTicketsCount }}</span>
            @else
                En cours
            @endif
        </div>
    </div>

    <!-- Temps d'attente estimé -->
    <div class="position-card">
        <div class="position-card-title">Temps d'attente</div>
        <div class="position-card-value">{{ $estimatedWaitTime }}</div>
    </div>

    <!-- Tickets en attente -->
    <div class="position-card">
        <div class="position-card-title">En attente</div>
        <div class="position-card-value">{{ $waitingTicketsCount }}</div>
    </div>
</div>

<style>
.position-card {
    @apply bg-white p-4 rounded-lg shadow-sm border border-gray-200 text-center;
}

.position-card-title {
    @apply text-sm font-medium text-gray-500 mb-1;
}

.position-card-value {
    @apply text-2xl font-bold text-gray-800;
}
</style>
