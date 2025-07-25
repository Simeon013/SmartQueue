<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\Queue;
use App\Models\TicketCycle;

class TicketCodeService
{
    /**
     * Génère le prochain code de ticket pour une file donnée
     */
    public function generateNextCode(Queue $queue): array
    {
        $currentCycle = TicketCycle::currentCycle();
        
        // Récupérer le dernier ticket de cette file dans le cycle actuel
        $lastTicket = Ticket::where('queue_id', $queue->id)
            ->where('cycle', $currentCycle)
            ->orderBy('id', 'desc')
            ->first();

        // Générer le préfixe basé sur l'ID de la file et le cycle
        $prefix = $this->getQueuePrefix($queue->id, $currentCycle);

        if (!$lastTicket || !preg_match('/^' . preg_quote($prefix, '/') . '-(\d+)$/', $lastTicket->code_ticket)) {
            // Premier ticket de cette file dans ce cycle
            return [
                'code' => $prefix . '-001',
                'cycle' => $currentCycle
            ];
        }

        // Extraire le numéro du dernier ticket
        preg_match('/^' . preg_quote($prefix, '/') . '-(\d+)$/', $lastTicket->code_ticket, $matches);
        $number = (int)($matches[1] ?? 0) + 1;

        return [
            'code' => sprintf('%s-%03d', $prefix, $number),
            'cycle' => $currentCycle
        ];
    }

    /**
     * Génère le préfixe de la file basé sur son ID et le cycle actuel
     */
    protected function getQueuePrefix(int $queueId, int $cycle): string
    {
        // Récupérer toutes les files actives (ouvertes ou en pause) triées par ID
        $activeQueues = \App\Models\Queue::whereIn('status', ['open', 'paused'])
            ->orderBy('id')
            ->pluck('id')
            ->toArray();
        
        // Trouver la position de cette file dans la liste des files actives
        $position = array_search($queueId, $activeQueues);
        
        // Si la file n'est pas trouvée (ne devrait pas arriver), utiliser l'ID
        if ($position === false) {
            $position = $queueId - 1;
        }
        
        // Convertir la position en lettre(s) : A=0, B=1, ..., Z=25, AA=26, AB=27, etc.
        $letters = '';
        $id = $position;
        
        do {
            $letters = chr(65 + ($id % 26)) . $letters;
            $id = (int)($id / 26) - 1;
        } while ($id >= 0);
        
        return $letters ?: 'A';
    }

    /**
     * Passe au cycle suivant
     */
    public function resetCycle(): int
    {
        return TicketCycle::nextCycle();
    }
}
