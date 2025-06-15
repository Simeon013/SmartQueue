<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Ticket;
use App\Models\Queue;

class RealtimeTicketStatus extends Component
{
    public Ticket $ticket;
    public Queue $queue;

    public $position;
    public $estimatedWaitTime;
    public $currentServingTicketCode;
    public $waitingTicketsCount;

    public function mount(Ticket $ticket, Queue $queue)
    {
        $this->ticket = $ticket;
        $this->queue = $queue;
        $this->updateTicketData();
    }

    public function render()
    {
        $this->updateTicketData(); // Update data on each render (including poll)
        return view('livewire.public.realtime-ticket-status');
    }

    private function updateTicketData()
    {
        // Re-fetch the latest ticket and queue data to ensure real-time accuracy
        $this->ticket->refresh();
        $this->queue->refresh();

        $this->position = $this->ticket->getPositionAttribute();
        $this->estimatedWaitTime = $this->ticket->getEstimatedWaitTimeAttribute();

        $currentServingTicket = $this->queue->tickets()->where('status', 'called')->orderBy('updated_at', 'desc')->first();
        $this->currentServingTicketCode = $currentServingTicket ? $currentServingTicket->code_ticket : 'Aucun ticket en cours de traitement';

        $this->waitingTicketsCount = $this->queue->tickets()->where('status', 'waiting')->count();
    }

    public function pauseTicket()
    {
        // Ensure the ticket belongs to the current session for security
        if ($this->ticket->session_id !== session()->getId()) {
            abort(403, 'Accès non autorisé pour mettre en pause ce ticket.');
        }

        // Update the ticket status to 'paused'
        $this->ticket->update(['status' => 'paused']);
        $this->updateTicketData(); // Refresh component data
        session()->flash('success', 'Votre ticket a été mis en pause momentanément.');
    }

    public function resumeTicket()
    {
        // Ensure the ticket belongs to the current session for security
        if ($this->ticket->session_id !== session()->getId()) {
            abort(403, 'Accès non autorisé pour reprendre ce ticket.');
        }

        // Update the ticket status to 'waiting'
        $this->ticket->update(['status' => 'waiting']);
        $this->updateTicketData(); // Refresh component data
        session()->flash('success', 'Votre ticket est de nouveau actif dans la file.');
    }

    public function cancelTicket()
    {
        // Ensure the ticket belongs to the current session for security
        if ($this->ticket->session_id !== session()->getId()) {
            abort(403, 'Accès non autorisé pour annuler ce ticket.');
        }

        $this->ticket->delete();
        session()->flash('success', 'Votre ticket a été annulé avec succès.');
        return redirect()->route('public.queues.index'); // Redirect after delete
    }
}
