<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Queue;
use App\Models\Ticket;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

class PublicTicketStatus extends Component
{
    public Queue $queue;
    public $ticket = null;

    public function mount(Queue $queue)
    {
        $this->queue = $queue;
        $this->loadTicket();
    }

    #[On('ticket-updated')]
    public function loadTicket()
    {
        $sessionId = session()->getId();
        Log::info('PublicTicketStatus: Loading ticket for session', ['session_id' => $sessionId]);

        $this->ticket = $this->queue->tickets()
            ->where('session_id', $sessionId)
            ->whereIn('status', ['waiting', 'called'])
            ->first();

        Log::info('PublicTicketStatus: Query result', ['ticket_found' => $this->ticket ? $this->ticket->toArray() : null]);
    }

    public function render()
    {
        // Calculate position if ticket exists and is waiting
        $position = null;
        if ($this->ticket && $this->ticket->status === 'waiting') {
            $position = $this->queue->tickets()
                ->where('status', 'waiting')
                ->where('created_at', '<', $this->ticket->created_at)
                ->count() + 1;
        }

        return view('livewire.public.public-ticket-status', [
            'position' => $position,
        ]);
    }
}