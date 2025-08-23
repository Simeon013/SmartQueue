<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Events\TicketStatusUpdated;
use App\Events\NotificationCreated;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckPausedTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:check-paused';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifie les tickets en pause et envoie des notifications si nécessaire';

    /**
     * Generate an appropriate notification message for a ticket
     *
     * @param Ticket $ticket The ticket to generate a message for
     * @param int $position The ticket's position in the queue
     * @return string The notification message
     */
    protected function getNotificationMessage(Ticket $ticket, int $position): string
    {
        $queueName = $ticket->queue->name;

        // Determine the appropriate message based on position
        if ($position === 1) {
            return "C'est bientôt votre tour ! Vous êtes le prochain dans la file '{$queueName}'. Préparez-vous à vous présenter.";
        }

        if ($position <= 3) {
            return "Votre tour approche ! Vous êtes en position {$position} dans la file '{$queueName}'. Préparez-vous à vous présenter bientôt.";
        }

        // Default message for other cases (shouldn't happen with current logic)
        return "Votre position dans la file '{$queueName}' est maintenant la {$position}ème. Veuillez vous présenter bientôt.";
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Début de la vérification des tickets en pause...');
        $startTime = microtime(true);

        try {
            // Récupérer les tickets en pause avec notification activée
            $tickets = Ticket::where('status', 'paused')
                ->where('notify_when_close', true)
                ->with(['queue', 'review']) // Charger les relations nécessaires
                ->get();

            $notifiedCount = 0;
            $skippedCount = 0;
            $errorCount = 0;

            $this->info(sprintf('Traitement de %d tickets en pause...', $tickets->count()));

            foreach ($tickets as $ticket) {
                try {
                    // Vérifier si le ticket doit être notifié
                    if ($ticket->shouldNotifyReturning()) {
                        $position = $ticket->getPositionAttribute();

                        // Préparer le message de notification
                        $message = $this->getNotificationMessage($ticket, $position);

                        // Créer une notification en base de données liée à la session
                        $notification = [
                            'ticket_id' => $ticket->id,
                            'ticket_code' => $ticket->code_ticket,
                            'queue_name' => $ticket->queue->name,
                            'position' => $position,
                            'message' => $message,
                            'session_id' => $ticket->session_id,
                            'type' => 'ticket_returning_soon',
                            'created_at' => now(),
                            'updated_at' => now()
                        ];

                        // Utiliser une transaction pour assurer l'intégrité des données
                        DB::transaction(function () use ($ticket, $notification, $position, $message) {
                            // Insérer la notification dans la table notifications
                            DB::table('notifications')->insert([
                                'id' => Str::uuid(),
                                'type' => 'App\\Notifications\\TicketReturningSoon',
                                'notifiable_type' => 'session',
                                'notifiable_id' => $ticket->session_id,
                                'data' => json_encode($notification),
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);

                            // Désactiver les notifications futures pour ce ticket
                            $ticket->update(['notify_when_close' => false]);
                        });

                        // Diffuser un événement pour informer le client en temps réel
                        event(new \App\Events\NotificationCreated(
                            sessionId: $ticket->session_id,
                            ticketId: $ticket->id,
                            queueId: $ticket->queue_id,
                            position: $position,
                            message: $message,
                            type: 'warning' // Type de notification pour le style
                        ));

                        // Diffuser également un événement de mise à jour du statut du ticket
                        event(new \App\Events\TicketStatusUpdated(
                            ticketId: $ticket->id,
                            queueId: $ticket->queue_id,
                            status: 'paused_notified'
                        ));

                        $this->line(sprintf(
                            'Notification créée pour le ticket #%s (File: %s, Position: %d)',
                            $ticket->code_ticket,
                            $ticket->queue->name,
                            $position
                        ));

                        $notifiedCount++;
                    } else {
                        $skippedCount++;
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    $this->error(sprintf(
                        'Erreur lors du traitement du ticket #%s: %s',
                        $ticket->code_ticket,
                        $e->getMessage()
                    ));

                    // Journaliser l'erreur complète pour le débogage
                    Log::error('Erreur lors du traitement du ticket en pause', [
                        'ticket_id' => $ticket->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            $executionTime = round(microtime(true) - $startTime, 2);
            $summary = sprintf(
                "Vérification terminée en %s secondes. %d notifications créées, %d tickets ignorés, %d erreurs.",
                $executionTime,
                $notifiedCount,
                $skippedCount,
                $errorCount
            );

            $this->info($summary);
            Log::info($summary);

            return $errorCount === 0 ? Command::SUCCESS : Command::FAILURE;

        } catch (\Exception $e) {
            $errorMessage = 'Erreur critique lors de la vérification des tickets en pause: ' . $e->getMessage();
            $this->error($errorMessage);
            Log::critical($errorMessage, [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Command::FAILURE;
        }
    }
}
