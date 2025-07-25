<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemSetting;
use App\Models\Queue;
use App\Enums\QueueStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AutoCloseQueues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queues:auto-close {--force : Force la fermeture même si les conditions ne sont pas remplies}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ferme automatiquement les files d\'attente selon les paramètres configurés';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Vérification de la fermeture automatique des files...');

        // Vérifier si la fermeture automatique est activée
        $autoCloseEnabled = SystemSetting::getValue('auto_close_enabled', false);

        if (!$autoCloseEnabled && !$this->option('force')) {
            $this->warn('La fermeture automatique n\'est pas activée.');
            return 0;
        }

        // Récupérer les paramètres
        $closeTime = SystemSetting::getValue('auto_close_time', '18:00');
        $closeDays = SystemSetting::getValue('auto_close_days', [0, 1, 2, 3, 4]); // Lundi à Vendredi par défaut

        $now = Carbon::now();
        $currentDayOfWeek = $now->dayOfWeek - 1; // Carbon: 0=Dimanche, 1=Lundi... Notre système: 0=Lundi

        // Ajuster pour notre système (0=Lundi, 6=Dimanche)
        if ($currentDayOfWeek < 0) {
            $currentDayOfWeek = 6; // Dimanche
        }

        $currentTime = $now->format('H:i');

        $this->info("Heure actuelle: {$currentTime}");
        $this->info("Jour actuel: " . $this->getDayName($currentDayOfWeek));
        $this->info("Heure de fermeture configurée: {$closeTime}");
        $this->info("Jours de fermeture: " . implode(', ', array_map([$this, 'getDayName'], $closeDays)));

        // Vérifier si c'est le bon jour et la bonne heure
        if (!in_array($currentDayOfWeek, $closeDays) && !$this->option('force')) {
            $this->warn('Aujourd\'hui n\'est pas un jour de fermeture configuré.');
            return 0;
        }

        if ($currentTime !== $closeTime && !$this->option('force')) {
            $this->warn("Il n'est pas encore l'heure de fermeture ({$closeTime}).");
            return 0;
        }

        // Fermer toutes les files actives (ouvertes ou en pause)
        $activeQueues = Queue::whereIn('status', [QueueStatus::OPEN->value, QueueStatus::PAUSED->value])->get();

        if ($activeQueues->isEmpty()) {
            $this->info('Aucune file active à fermer.');
            return 0;
        }

        $this->info("Fermeture de {$activeQueues->count()} file(s) d'attente...");

        $closedCount = 0;
        foreach ($activeQueues as $queue) {
            // Sauvegarder l'ancien statut pour le message de log
            $oldStatus = $queue->status->label();
            
            $queue->update(['status' => QueueStatus::CLOSED]);
            $this->line("✓ File '{$queue->name}' fermée (était: {$oldStatus})");
            $closedCount++;
        }
        
        // Réinitialiser le compteur de cycle
        if ($closedCount > 0) {
            $ticketService = app(\App\Services\TicketCodeService::class);
            $newCycle = $ticketService->resetCycle();
            $this->info("✅ Compteur de tickets réinitialisé. Nouveau cycle : {$newCycle}");
        }

        $this->info("✅ {$closedCount} file(s) d'attente fermée(s) avec succès.");

        // Log de l'action
        Log::info("Fermeture automatique des files d'attente effectuée", [
            'closed_count' => $closedCount,
            'time' => $now->toDateTimeString(),
            'queues' => $activeQueues->pluck('name')->toArray()
        ]);

        return 0;
    }

    /**
     * Obtenir le nom du jour en français
     */
    private function getDayName(int $dayIndex): string
    {
        $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        return $days[$dayIndex] ?? 'Inconnu';
    }
}
