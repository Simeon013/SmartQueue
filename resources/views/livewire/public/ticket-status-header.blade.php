@props(['ticket', 'ticketStatus', 'statusInfo'])

<div class="bg-white rounded-xl shadow-md overflow-hidden mb-6">
    <div class="p-6">
        <!-- En-tête avec statut -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0 {{ $statusInfo['bgColor'] }} rounded-full p-3">
                    <i class="fas {{ $statusInfo['icon'] }} text-2xl {{ $statusInfo['textColor'] }}"></i>
                </div>
                <div class="ml-4">
                    <h2 class="text-xl font-bold text-gray-900">{{ $ticket->code_ticket }}</h2>
                    <p class="text-sm {{ $statusInfo['textColor'] }} font-medium">
                        {{ $statusInfo['message'] }}
                    </p>
                </div>
            </div>
            <div class="mt-4 md:mt-0">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusInfo['bgColor'] }} {{ $statusInfo['textColor'] }}">
                    {{ ucfirst($ticket->status) }}
                </span>
            </div>
        </div>
    </div>
</div>
