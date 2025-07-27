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

    public Ticket $ticket;
    public Queue $queue;
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
        try {
            // Rafraîchir le ticket depuis la base de données pour avoir les dernières données
            $this->ticket->refresh();
            
            // Stocker le statut actuel du ticket dans la session
            session(['last_ticket_status' => $this->ticket->status]);

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

            // Mettre à jour le temps de traitement si le ticket est en cours
            if ($this->ticket->status === 'in_progress' && $this->ticket->processing_time) {
                $this->processingTime = $this->formatDuration($this->ticket->processing_time);
            } else {
                $this->processingTime = '--:--';
            }

            // Récupérer le ticket actuellement en cours de traitement
            $this->currentServingTicketCode = 'Aucun ticket en cours de traitement';
            $currentServingTicket = $this->queue->tickets()
                ->where('id', $this->ticket->id)
                ->where('status', 'in_progress')
                ->first();

            if ($currentServingTicket) {
                $this->currentServingTicketCode = $currentServingTicket->code_ticket;
            }

            $this->waitingTicketsCount = $this->queue->tickets()
                ->where('status', 'waiting')
                ->count();
                
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour des données du ticket', [
                'ticket_id' => $this->ticket->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Réinitialiser les valeurs en cas d'erreur
            $this->position = 0;
            $this->estimatedWaitTime = '--:--';
            $this->actualWaitTime = '--:--';
            $this->processingTime = '--:--';
            $this->currentServingTicketCode = 'Erreur de chargement';
            $this->waitingTicketsCount = 0;
        }
    }

    public function pauseTicket()
    {
        try {
            // Vérifier que le ticket appartient à la session en cours pour des raisons de sécurité
            if ($this->ticket->session_id !== session()->getId()) {
                abort(403, 'Accès non autorisé pour mettre en pause ce ticket.');
            }

            // Mettre à jour le statut du ticket
            $this->ticket->update(['status' => 'paused']);
            $this->updateTicketData(); // Rafraîchir les données du composant
            
            session()->flash('success', 'Votre ticket a été mis en pause momentanément.');
            return redirect()->route('public.ticket.status', [
                'queue_code' => $this->queue->code, 
                'ticket_code' => $this->ticket->code_ticket
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise en pause du ticket', [
                'ticket_id' => $this->ticket->id ?? null,
                'error' => $e->getMessage()
            ]);
            
            session()->flash('error', 'Une erreur est survenue lors de la mise en pause du ticket.');
        }
    }

    public function resumeTicket()
    {
        try {
            // Vérifier que le ticket appartient à la session en cours pour des raisons de sécurité
            if ($this->ticket->session_id !== session()->getId()) {
                abort(403, 'Accès non autorisé pour reprendre ce ticket.');
            }

            // Mettre à jour le statut du ticket
            $this->ticket->update(['status' => 'waiting']);
            $this->updateTicketData(); // Rafraîchir les données du composant
            
            session()->flash('success', 'Votre ticket est de nouveau actif dans la file.');
            return redirect()->route('public.ticket.status', [
                'queue_code' => $this->queue->code, 
                'ticket_code' => $this->ticket->code_ticket
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la reprise du ticket', [
                'ticket_id' => $this->ticket->id ?? null,
                'error' => $e->getMessage()
            ]);
            
            session()->flash('error', 'Une erreur est survenue lors de la reprise du ticket.');
        }
    }

    public function cancelTicket()
    {
        try {
            // Vérifier que le ticket appartient à la session en cours pour des raisons de sécurité
            if ($this->ticket->session_id !== session()->getId()) {
                abort(403, 'Accès non autorisé pour annuler ce ticket.');
            }

            // Marquer le ticket comme annulé au lieu de le supprimer
            $this->ticket->update([
                'status' => 'cancelled',
                'handled_at' => now()
            ]);
            
            session()->flash('success', 'Votre ticket a été annulé avec succès.');
            return redirect()->route('public.queues.index');
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'annulation du ticket', [
                'ticket_id' => $this->ticket->id ?? null,
                'error' => $e->getMessage()
            ]);
            
            session()->flash('error', 'Une erreur est survenue lors de l\'annulation du ticket.');
            return back();
        }
    }
}
