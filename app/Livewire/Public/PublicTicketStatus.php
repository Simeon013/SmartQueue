<?php

namespace App\Livewire\Public;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Queue;
use App\Models\Ticket;
use App\Traits\FormatsDuration;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class PublicTicketStatus extends Component
{
    use FormatsDuration;
    
    public $ticket = null;
    public $queue;
    public $position = 0;
    public $estimatedWaitTime = '--:--';
    public $actualWaitTime = '--:--';

    public function mount(Queue $queue)
    {
        $this->queue = $queue;
        $this->loadTicket();
    }

    #[On('ticket-updated')]
    public function loadTicket()
    {
        $sessionId = Session::getId();

        $this->ticket = $this->queue->tickets()
            ->where('session_id', $sessionId)
            ->whereIn('status', ['waiting', 'in_progress', 'paused'])
            ->with('handler')
            ->first();
            
        if ($this->ticket) {
            // Mettre à jour la position dans la file d'attente
            $this->position = $this->ticket->position;
            
            // Mettre à jour le temps d'attente estimé
            if ($this->ticket->status === 'waiting' && $this->ticket->estimated_wait_time) {
                $this->estimatedWaitTime = $this->formatDuration($this->ticket->estimated_wait_time);
            } else {
                $this->estimatedWaitTime = '--:--';
            }
            
            // Mettre à jour le temps d'attente réel
            if ($this->ticket->actual_wait_time) {
                $this->actualWaitTime = $this->formatDuration($this->ticket->actual_wait_time);
            } else {
                $this->actualWaitTime = '--:--';
            }
        } else {
            $this->position = 0;
            $this->estimatedWaitTime = '--:--';
            $this->actualWaitTime = '--:--';
        }

        Log::info('PublicTicketStatus: Query result', [
            'ticket_found' => $this->ticket ? $this->ticket->toArray() : null,
            'position' => $this->position,
            'estimated_wait_time' => $this->estimatedWaitTime,
            'actual_wait_time' => $this->actualWaitTime
        ]);
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