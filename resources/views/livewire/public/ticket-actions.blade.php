@props(['ticket', 'ticketStatus'])

<div class="mt-6 space-y-4">
    @if($ticketStatus === 'waiting')
        <button 
            wire:click="pauseTicket" 
            class="w-full flex items-center justify-center px-4 py-3 border border-yellow-300 text-sm font-medium rounded-md text-yellow-700 bg-yellow-50 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
        >
            <i class="fas fa-pause mr-2"></i>
            Mettre en pause
        </button>
        
        <button 
            wire:click="cancelTicket" 
            class="w-full flex items-center justify-center px-4 py-3 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
        >
            <i class="fas fa-times-circle mr-2"></i>
            Annuler le ticket
        </button>
    
    @elseif($ticketStatus === 'paused')
        <button 
            wire:click="resumeTicket" 
            class="w-full flex items-center justify-center px-4 py-3 border border-green-300 text-sm font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
        >
            <i class="fas fa-play mr-2"></i>
            Reprendre la file
        </button>
        
        <button 
            wire:click="cancelTicket" 
            class="w-full flex items-center justify-center px-4 py-3 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
        >
            <i class="fas fa-times-circle mr-2"></i>
            Annuler le ticket
        </button>
    
    @elseif(in_array($ticketStatus, ['served', 'skipped']))
        <a 
            href="{{ route('public.queues.index') }}" 
            class="block w-full text-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
            <i class="fas fa-home mr-2"></i>
            Retour à l'accueil
        </a>
    @endif
    
    @if($ticketStatus === 'cancelled')
        <div class="p-4 bg-red-50 rounded-md border border-red-200">
            <p class="text-sm text-red-700 text-center">
                Ce ticket a été annulé. Vous pouvez prendre un nouveau ticket si nécessaire.
            </p>
        </div>
    @endif
</div>
