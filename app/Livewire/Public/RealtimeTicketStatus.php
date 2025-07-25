<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Ticket;
use App\Models\Queue;
use App\Traits\FormatsDuration;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class RealtimeTicketStatus extends Component
{
    use FormatsDuration;

    public $ticket;
    public $queue;
    public $position = 0;
    public $estimatedWaitTime = '--:--';
    public $actualWaitTime = '--:--';
    public $processingTime = '--:--';
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
        $sessionId = Session::getId();

        // Récupérer le ticket avec les relations nécessaires
        $ticket = $this->queue->tickets()
            ->where('session_id', $sessionId)
            ->whereIn('status', ['waiting', 'in_progress'])
            ->with('handler')
            ->first();

        if ($ticket) {
            $this->ticket = $ticket;

            // Mettre à jour la position dans la file d'attente
            $this->position = $ticket->position;

            // Mettre à jour le temps d'attente estimé
            if ($ticket->status === 'waiting' && $ticket->estimated_wait_time) {
                $this->estimatedWaitTime = $this->formatDuration($ticket->estimated_wait_time);
            } else {
                $this->estimatedWaitTime = '--:--';
            }

            // Mettre à jour le temps d'attente réel
            if ($ticket->actual_wait_time) {
                $this->actualWaitTime = $this->formatDuration($ticket->actual_wait_time);
            } else {
                $this->actualWaitTime = '--:--';
            }

            // Mettre à jour le temps de traitement si le ticket est en cours
            if ($ticket->status === 'in_progress' && $ticket->processing_time) {
                $this->processingTime = $this->formatDuration($ticket->processing_time);
            } else {
                $this->processingTime = '--:--';
            }
        } else {
            $this->ticket = null;
            $this->position = 0;
            $this->estimatedWaitTime = '--:--';
            $this->actualWaitTime = '--:--';
            $this->processingTime = '--:--';
        }

        // Récupérer le ticket actuellement en cours de traitement par l'utilisateur
        $currentServingTicket = $this->queue->tickets()
            ->where('id', $this->ticket->id)
            ->where('status', 'in_progress')
            ->first();

        $this->currentServingTicketCode = $currentServingTicket ? $currentServingTicket->code_ticket : 'Aucun ticket en cours de traitement';

        $this->waitingTicketsCount = $this->queue->tickets()
            ->where('status', 'waiting')
            ->count();
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
