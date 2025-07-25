<?php
namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Queue;
use App\Models\Ticket;
use App\Models\User;
use App\Traits\FormatsDuration;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class QueueTickets extends Component
{
    use WithPagination, FormatsDuration;

    public Queue $queue;
    public $ticketName = '';
    public $ticketEmail = '';
    public $ticketPhone = '';
    public $ticketNotes = '';
    
    // Propriétés pour le tri
    public $sortField = 'created_at';
    public $sortDirection = 'asc';

    protected $rules = [
        'ticketName' => 'required|min:3',
        'ticketEmail' => 'nullable|email',
        'ticketPhone' => 'nullable|string',
        'ticketNotes' => 'nullable|string',
    ];

    public function mount(Queue $queue)
    {
        $this->queue = $queue;
    }

    /**
     * Génère le prochain code de ticket en utilisant le service de génération
     */
    private function generateTicketCode()
    {
        $ticketService = app(\App\Services\TicketCodeService::class);
        $result = $ticketService->generateNextCode($this->queue);
        
        return $result['code'];
    }

    public function createTicket()
    {
        try {
            // Valider les données du formulaire
            $this->validate();

            // Commencer une transaction
            DB::beginTransaction();

            // Générer le code du ticket et récupérer le cycle
            $ticketService = app(\App\Services\TicketCodeService::class);
            $result = $ticketService->generateNextCode($this->queue);
            $codeTicket = $result['code'];
            $cycle = $result['cycle'];

            // Créer le ticket
            $ticket = $this->queue->tickets()->create([
                'code_ticket' => $codeTicket,
                'cycle' => $cycle,
                'name' => $this->ticketName,
                'email' => $this->ticketEmail ?: null,
                'phone' => $this->ticketPhone ?: null,
                'notes' => $this->ticketNotes ?: null,
                'status' => 'waiting',
                'session_id' => session()->getId(),
            ]);

            DB::commit();

            // Réinitialiser le formulaire
            $this->reset(['ticketName', 'ticketEmail', 'ticketPhone', 'ticketNotes']);

            // Notifier le succès
            session()->flash('success', 'Ticket ' . $codeTicket . ' créé avec succès !');

            // Émettre l'événement
            $this->dispatch('ticket-created');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la création du ticket : ' . $e->getMessage());

            // Log l'erreur pour le debug
            Log::error('Erreur création ticket: ' . $e->getMessage(), [
                'queue_id' => $this->queue->id,
                'data' => [
                    'name' => $this->ticketName,
                    'email' => $this->ticketEmail,
                    'phone' => $this->ticketPhone,
                ]
            ]);
        }
    }

    public function updateTicketStatus(Ticket $ticket, $status)
    {
        try {
            $user = Auth::user();
            
            switch ($status) {
                case 'in_progress':
                    if ($ticket->is_being_handled && !$ticket->isHandledBy($user)) {
                        session()->flash('error', 'Ce ticket est déjà en cours de traitement par un autre agent.');
                        return;
                    }
                    $ticket->assignTo($user);
                    session()->flash('success', 'Ticket ' . $ticket->code_ticket . ' pris en charge avec succès');
                    break;
                    
                case 'served':
                    $ticket->markAsServed();
                    session()->flash('success', 'Ticket ' . $ticket->code_ticket . ' marqué comme traité');
                    break;
                    
                case 'skipped':
                    $ticket->markAsSkipped();
                    session()->flash('success', 'Ticket ' . $ticket->code_ticket . ' marqué comme ignoré');
                    break;
                    
                case 'waiting':
                default:
                    $ticket->release();
                    session()->flash('success', 'Ticket ' . $ticket->code_ticket . ' remis en attente');
                    break;
            }
            
            $this->dispatch('ticket-updated');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du statut du ticket: ' . $e->getMessage());
            session()->flash('error', 'Erreur lors de la mise à jour du statut: ' . $e->getMessage());
        }
    }

    public function deleteTicket(Ticket $ticket)
    {
        try {
            $ticket->delete();
            $this->dispatch('ticket-deleted');
            session()->flash('success', 'Ticket supprimé avec succès');
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de la suppression du ticket');
        }
    }

    public function getStatsProperty()
    {
        return [
            'total_tickets' => $this->queue->tickets()->count(),
            'active_tickets' => $this->queue->tickets()->where('status', 'waiting')->count(),
            'average_wait_time' => $this->queue->tickets()
                ->whereNotNull('served_at')
                ->whereNotNull('called_at')
                ->avg(DB::raw('TIMESTAMPDIFF(SECOND, called_at, served_at)')),
            'processed_tickets' => [
                'total' => $this->queue->tickets()
                    ->whereIn('status', ['served', 'skipped'])
                    ->count(),
                'served' => $this->queue->tickets()
                    ->where('status', 'served')
                    ->count(),
                'skipped' => $this->queue->tickets()
                    ->where('status', 'skipped')
                    ->count(),
            ]
        ];
    }

    public function getCurrentTicketProperty()
    {
        $user = Auth::user();
        
        // Vérifier d'abord si l'utilisateur a déjà un ticket en cours de traitement
        $userTicket = $this->queue->tickets()
            ->where('status', 'in_progress')
            ->where('handled_by', $user->id)
            ->orderBy('handled_at', 'asc')
            ->first();
            
        if ($userTicket) {
            Log::info('Ticket en cours trouvé pour l\'utilisateur', ['ticket' => $userTicket->toArray()]);
            return $userTicket;
        }
        
        // Sinon, chercher un ticket en attente
        $waitingTicket = $this->queue->tickets()
            ->where('status', 'waiting')
            ->orderBy('created_at', 'asc')
            ->first();

        if ($waitingTicket) {
            Log::info('Ticket en attente trouvé', ['ticket' => $waitingTicket->toArray()]);
            return $waitingTicket;
        }

        Log::info('Aucun ticket disponible');
        return null;
    }

    public function quickAction($action)
    {
        if (!$this->currentTicket) {
            session()->flash('error', 'Aucun ticket à traiter');
            return;
        }

        try {
            $user = Auth::user();
            
            switch ($action) {
                case 'take':
                    $this->updateTicketStatus($this->currentTicket, 'in_progress');
                    break;
                    
                case 'validate':
                    $this->updateTicketStatus($this->currentTicket, 'served');
                    break;

                case 'absent':
                    $this->updateTicketStatus($this->currentTicket, 'skipped');
                    break;

                case 'release':
                    $this->updateTicketStatus($this->currentTicket, 'waiting');
                    break;
            }

            $this->dispatch('ticket-updated');
            $this->dispatch('$refresh');

        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors du traitement du ticket: ' . $e->getMessage());
            Log::error('Erreur action rapide ticket: ' . $e->getMessage());
        }
    }

    // Méthode pour trier les colonnes
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }
    
    public function render()
    {
        $user = Auth::user();
        
        // Récupérer les tickets en attente et en cours de traitement
        $query = $this->queue->tickets()
            ->whereIn('status', ['waiting', 'in_progress'])
            ->with('handler'); // Charger la relation handler pour afficher l'agent qui gère le ticket
            
        // Appliquer le tri
        if ($this->sortField === 'status') {
            $query->orderByRaw("CASE 
                WHEN status = 'in_progress' THEN 1 
                WHEN status = 'waiting' THEN 2
            END", $this->sortDirection);
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }
        
        // Calcul des temps moyens avec cache
        $cacheKey = "queue_{$this->queue->id}_stats";
        $stats = Cache::remember($cacheKey, now()->addMinute(), function() {
            // Temps d'attente moyen (création -> prise en charge)
            $avgWaitTime = $this->queue->tickets()
                ->whereIn('status', ['served', 'skipped'])
                ->whereNotNull('handled_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, handled_at)) as avg_wait_time')
                ->value('avg_wait_time');

            // Temps de traitement moyen (prise en charge -> service)
            $avgProcessingTime = $this->queue->tickets()
                ->where('status', 'served')
                ->whereNotNull('handled_at')
                ->whereNotNull('served_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, handled_at, served_at)) as avg_process_time')
                ->value('avg_process_time');

            // Temps total moyen (création -> service)
            $avgTotalTime = $this->queue->tickets()
                ->where('status', 'served')
                ->whereNotNull('served_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, served_at)) as avg_total_time')
                ->value('avg_total_time');

            return [
                'total_tickets' => $this->queue->tickets()->count(),
                'active_tickets' => $this->queue->tickets()->whereIn('status', ['waiting', 'in_progress'])->count(),
                'waiting_tickets' => $this->queue->tickets()->where('status', 'waiting')->count(),
                'in_progress_tickets' => $this->queue->tickets()->where('status', 'in_progress')->count(),
                'served_tickets' => $this->queue->tickets()->where('status', 'served')->count(),
                'skipped_tickets' => $this->queue->tickets()->where('status', 'skipped')->count(),
                'average_wait_time' => $avgWaitTime,
                'average_processing_time' => $avgProcessingTime,
                'average_total_time' => $avgTotalTime,
                'processed_tickets' => [
                    'total' => $this->queue->tickets()->whereIn('status', ['served', 'skipped'])->count(),
                    'served' => $this->queue->tickets()->where('status', 'served')->count(),
                    'skipped' => $this->queue->tickets()->where('status', 'skipped')->count(),
                ]
            ];
        });
            
        return view('livewire.admin.queue-tickets', [
            'tickets' => $query->paginate(10),
            'stats' => $stats,
            'currentUser' => $user,
        ]);
    }
}
