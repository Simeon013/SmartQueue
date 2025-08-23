<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Ticket;
use App\Models\Queue;
use App\Models\Review;
use App\Traits\FormatsDuration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

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

    // Propriétés pour les notifications
    public $notifications = [];
    public $showNotificationAlert = false;
    public $latestNotification = null;

    // Propriétés pour le formulaire d'avis
    public $showReviewForm = false;
    public $rating = 0;
    public $comment = '';
    public $reviewSubmitted = false;

    // Propriété pour la modale d'annulation
    public $showCancelModal = false;

    /**
     * Écouteurs d'événements Livewire
     */
    protected function getListeners()
    {
        $listeners = [
            "echo-private:queue.{$this->queue->id},TicketStatusUpdated" => 'handleTicketStatusUpdated',
            "echo-private:session.{$this->ticket->session_id},NotificationCreated" => 'handleNewNotification',
            'dismissNotification' => 'dismissNotification',
        ];

        // Si le ticket est en pause, on écoute aussi les mises à jour de la file d'attente
        if ($this->ticket->status === 'paused') {
            $listeners["echo-private:queue.{$this->queue->id},.ticket.updated"] = 'handleTicketUpdated';
        }

        return $listeners;
    }

    /**
     * Gère la mise à jour du statut d'un ticket
     */
    public function handleTicketStatusUpdated($event)
    {
        $this->updateTicketData();
    }

    /**
     * Gère les nouvelles notifications
     */
    public function handleNewNotification($event)
    {
        $this->checkForNotifications();
    }

    /**
     * Gère les mises à jour des tickets dans la file d'attente
     */
    public function handleTicketUpdated($event)
    {
        // Mettre à jour les données du ticket actuel
        $this->ticket->refresh();

        // Si le ticket n'est plus en pause, arrêter d'écouter les mises à jour
        if ($this->ticket->status !== 'paused') {
            $this->getListeners(); // Recharger les écouteurs
        }

        // Mettre à jour les données affichées
        $this->updateTicketData();

        // Vérifier s'il y a de nouvelles notifications
        $this->checkForNotifications();
    }

    public function mount(Ticket $ticket, Queue $queue)
    {
        $this->ticket = $ticket;
        $this->queue = $queue;
        $this->updateTicketData();
        $this->checkForNotifications();
    }

    /**
     * Vérifie et récupère les notifications non lues
     */
    public function checkForNotifications()
    {
        if (!isset($this->ticket->session_id)) {
            return;
        }

        try {
            // Récupérer les notifications non lues pour cette session
            $notifications = DB::table('notifications')
                ->where('notifiable_type', 'session')
                ->where('notifiable_id', $this->ticket->session_id)
                ->whereNull('read_at')
                ->orderBy('created_at', 'desc')
                ->get();

            if ($notifications->isNotEmpty()) {
                // Transformer les notifications en tableau avec les données nécessaires
                $this->notifications = $notifications->map(function($notification) {
                    $data = json_decode($notification->data, true);
                    return [
                        'id' => $notification->id,
                        'message' => $data['message'] ?? 'Notification',
                        'created_at' => $notification->created_at,
                        'data' => $data,
                        'type' => $data['type'] ?? 'info'
                    ];
                })->toArray();

                // Mettre à jour la notification la plus récente pour l'affichage
                $this->latestNotification = $this->notifications[0] ?? null;
                $this->showNotificationAlert = true;

                // Marquer les notifications comme lues dans la base de données
                $notificationIds = $notifications->pluck('id')->toArray();
                if (!empty($notificationIds)) {
                    DB::table('notifications')
                        ->whereIn('id', $notificationIds)
                        ->update(['read_at' => now()]);
                }
            }
        } catch (\Exception $e) {
            // Journaliser l'erreur mais ne pas interrompre le flux
            Log::error('Erreur lors de la vérification des notifications: ' . $e->getMessage());
        }
    }

    public function dismissNotification()
    {
        $this->showNotificationAlert = false;
    }

    public function render()
    {
        $this->updateTicketData(); // Update data on each render (including poll)
        $this->checkForNotifications(); // Vérifier les nouvelles notifications

        // Vérifier si on doit afficher le formulaire d'avis
        if ($this->ticket->status === 'served' && !$this->ticket->has_review && !$this->reviewSubmitted) {
            $this->showReviewForm = true;
        } else {
            $this->showReviewForm = false;
        }

        return view('livewire.public.realtime-ticket-status');
    }

    /**
     * Soumettre un avis pour le ticket
     */
    public function submitReview()
    {
        try {
            // Valider les données du formulaire
            $validatedData = $this->validate([
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
            ]);

            // Vérifier si un avis existe déjà pour ce ticket
            if ($this->ticket->review) {
                // Mettre à jour l'avis existant
                $this->ticket->review->update([
                    'rating' => $validatedData['rating'],
                    'comment' => $validatedData['comment'] ?? null,
                    'submitted_at' => now(),
                ]);

                Log::info('Avis mis à jour pour le ticket #' . $this->ticket->id);
            } else {
                // Créer un nouvel avis via la relation
                $review = $this->ticket->review()->create([
                    'rating' => $validatedData['rating'],
                    'comment' => $validatedData['comment'] ?? null,
                    'submitted_at' => now(),
                    'token' => Str::uuid(), // Ajout manuel du token
                ]);

                if (!$review) {
                    throw new \Exception('Échec de la création de l\'avis');
                }

                Log::info('Nouvel avis créé pour le ticket #' . $this->ticket->id);
            }

            // Mettre à jour l'état du composant
            $this->reviewSubmitted = true;
            $this->showReviewForm = false;

            // Rafraîchir la relation pour mettre à jour has_review
            $this->ticket->refresh();

            // Afficher un message de succès
            session()->flash('review_submitted', 'Merci pour votre avis !');

            // Réinitialiser les champs du formulaire
            $this->reset(['rating', 'comment']);

            return true;

        } catch (\Exception $e) {
            // En cas d'erreur, afficher un message d'erreur
            $errorMessage = 'Une erreur est survenue lors de la soumission de votre avis. Veuillez réessayer.';
            session()->flash('error', $errorMessage);
            Log::error('Erreur lors de la soumission d\'un avis: ' . $e->getMessage());

            // Réafficher le formulaire avec les données saisies
            $this->showReviewForm = true;

            return false;
        }
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
                ->orWhere('status', 'paused')
                ->count();
            // dd($this->waitingTicketsCount);

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
