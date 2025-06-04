<?php
namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Queue;
use App\Models\Ticket;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueueTickets extends Component
{
    use WithPagination;

    public Queue $queue;
    public $ticketName = '';
    public $ticketEmail = '';
    public $ticketPhone = '';
    public $ticketNotes = '';

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

    private function generateTicketCode()
    {
        // Récupérer le dernier ticket de la file
        $lastTicket = $this->queue->tickets()->latest('id')->first();

        if (!$lastTicket) {
            return 'A-01';
        }

        // Extraire la lettre et le numéro du dernier code
        preg_match('/^([A-Z])-(\d+)$/', $lastTicket->code_ticket, $matches);

        if (!$matches) {
            return 'A-01';
        }

        $letter = $matches[1];
        $number = (int)$matches[2];

        // Si on atteint 99, passer à la lettre suivante
        if ($number >= 99) {
            $letter = chr(ord($letter) + 1);
            $number = 1;
        } else {
            $number++;
        }

        // Formater le nouveau code
        return sprintf('%s-%02d', $letter, $number);
    }

    public function createTicket()
    {
        try {
            $this->validate();

            DB::beginTransaction();

            // Générer le code du ticket
            $codeTicket = $this->generateTicketCode();

            // Créer le ticket
            $ticket = $this->queue->tickets()->create([
                'code_ticket' => $codeTicket,
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
            $updateData = ['status' => $status];

            if ($status === 'called') {
                $updateData['called_at'] = now();
            } elseif ($status === 'served') {
                $updateData['served_at'] = now();
            }

            $ticket->update($updateData);
            $this->dispatch('ticket-status-updated');

        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de la mise à jour du statut');
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
        // D'abord chercher un ticket appelé mais pas encore servi
        $calledTicket = $this->queue->tickets()
            ->where('status', 'called')
            ->orderBy('called_at', 'asc')
            ->first();

        if ($calledTicket) {
            Log::info('Ticket appelé trouvé', ['ticket' => $calledTicket->toArray()]);
            return $calledTicket;
        }

        // Sinon, prendre le prochain ticket en attente
        $waitingTicket = $this->queue->tickets()
            ->where('status', 'waiting')
            ->orderBy('created_at', 'asc')
            ->first();

        if ($waitingTicket) {
            Log::info('Ticket en attente trouvé', ['ticket' => $waitingTicket->toArray()]);
            return $waitingTicket;
        }

        Log::info('Aucun ticket trouvé');
        return null;
    }

    public function quickAction($action)
    {
        if (!$this->currentTicket) {
            session()->flash('error', 'Aucun ticket à traiter');
            return;
        }

        try {
            switch ($action) {
                case 'validate':
                    $this->updateTicketStatus($this->currentTicket, 'served');
                    session()->flash('success', 'Ticket ' . $this->currentTicket->code_ticket . ' validé avec succès');
                    break;

                case 'absent':
                    $this->updateTicketStatus($this->currentTicket, 'skipped');
                    session()->flash('success', 'Ticket ' . $this->currentTicket->code_ticket . ' marqué comme absent');
                    break;

                case 'call':
                    $this->updateTicketStatus($this->currentTicket, 'called');
                    session()->flash('success', 'Ticket ' . $this->currentTicket->code_ticket . ' appelé');
                    break;

                case 'recall':
                    // Réinitialiser le statut à 'waiting' et mettre à jour called_at
                    $this->currentTicket->update([
                        'status' => 'waiting',
                        'called_at' => null
                    ]);
                    session()->flash('success', 'Ticket ' . $this->currentTicket->code_ticket . ' remis en attente');
                    break;
            }

            $this->dispatch('ticket-status-updated');
            $this->dispatch('$refresh');

        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors du traitement du ticket');
            Log::error('Erreur action rapide ticket: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.queue-tickets', [
            'tickets' => $this->queue->tickets()
                ->whereIn('status', ['waiting', 'called'])
                ->orderByRaw("CASE
                    WHEN status = 'waiting' THEN 1
                    WHEN status = 'called' THEN 2
                    ELSE 3
                END")
                ->orderBy('created_at', 'asc')
                ->paginate(10),
            'stats' => $this->stats,
        ]);
    }
}
