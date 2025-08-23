<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Events\TicketStatusUpdated;

class QueueController extends Controller
{
    public function show(Queue $queue)
    {
        // Vérifier si la file est accessible
        if ($queue->status->value === 'closed' || $queue->status->value === 'blocked') {
            $statusMessage = $queue->status->value === 'closed'
                ? 'Cette file d\'attente est actuellement fermée.'
                : 'Cette file d\'attente est actuellement bloquée.';

            return redirect()->route('public.queues.index')
                ->with('error', $statusMessage);
        }

        $sessionId = session()->getId();
        $ticket = $queue->tickets()
            ->where('session_id', $sessionId)
            ->whereIn('status', ['waiting', 'called'])
            ->first();

        if ($ticket) {
            return redirect()->route('public.ticket.status', ['queue_code' => $queue->code, 'ticket_code' => $ticket->code_ticket]);
        }

        $waitingTicketsCount = $queue->tickets()->where('status', 'waiting', 'paused')->count();
        return view('public.queues.show', compact('queue', 'waitingTicketsCount'));
    }

    public function join(Request $request, Queue $queue)
    {
        // Vérifier si la file est ouverte
        if ($queue->status->value !== 'open') {
            $errorMessage = match($queue->status->value) {
                'paused' => 'Cette file d\'attente est actuellement en pause. Veuillez réessayer ultérieurement.',
                'blocked' => 'Cette file d\'attente est actuellement bloquée.',
                'closed' => 'Cette file d\'attente est actuellement fermée.',
                default => 'Impossible de rejoindre cette file d\'attente pour le moment.'
            };

            Log::info('Tentative de rejoindre une file non ouverte', [
                'queue_id' => $queue->id,
                'status' => $queue->status->value,
                'error' => $errorMessage
            ]);

            return redirect()
                ->back()
                ->with('error', $errorMessage);
        }

        $sessionId = $request->session()->getId();
        $existingTicket = $queue->tickets()
            ->where('session_id', $sessionId)
            ->whereIn('status', ['waiting', 'called'])
            ->first();

        if ($existingTicket) {
            return redirect()->route('public.ticket.status', ['queue_code' => $queue->code, 'ticket_code' => $existingTicket->code_ticket])
                ->with('error', 'Vous avez déjà un ticket en cours pour cette file.');
        }

        // Utiliser le service de génération de codes de tickets
        $ticketService = app(\App\Services\TicketCodeService::class);
        $result = $ticketService->generateNextCode($queue);

        $ticket = $queue->tickets()->create([
            'queue_id' => $queue->id,
            'code_ticket' => $result['code'],
            'cycle' => $result['cycle'],
            'status' => 'waiting',
            'session_id' => $sessionId,
        ]);

        // Diffuser un événement de mise à jour du statut du ticket
        event(new TicketStatusUpdated($ticket->id, $queue->id, 'waiting'));

        return redirect()->route('public.ticket.status', ['queue_code' => $queue->code, 'ticket_code' => $ticket->code_ticket])
            ->with('success', 'Votre ticket ' . $ticket->code_ticket . ' a été créé avec succès !');
    }

    public function ticketStatus($queue_code, $ticket_code)
    {
        $queue = Queue::where('code', $queue_code)->firstOrFail();
        $ticket = Ticket::where('code_ticket', $ticket_code)
            ->where('queue_id', $queue->id)
            ->whereIn('status', ['waiting', 'in_progress', 'paused', 'cancelled', 'served', 'skipped'])
            ->firstOrFail();

        if ($ticket->session_id !== session()->getId()) {
            abort(403, 'Accès non autorisé au ticket.');
        }

        // Récupérer les notifications pour cette session
        $notifications = DB::table('notifications')
            ->where('notifiable_type', 'session')
            ->where('notifiable_id', $ticket->session_id)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($notification) {
                $data = json_decode($notification->data, true);
                return [
                    'id' => $notification->id,
                    'message' => $data['message'] ?? 'Notification',
                    'created_at' => $notification->created_at,
                    'data' => $data
                ];
            });

        // Marquer les notifications comme lues
        DB::table('notifications')
            ->where('notifiable_type', 'session')
            ->where('notifiable_id', $ticket->session_id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('public.tickets.status', compact('queue', 'ticket', 'notifications'));
    }

    public function cancelTicket(Ticket $ticket)
    {
        // Vérifier que le ticket appartient à la session en cours pour des raisons de sécurité
        if ($ticket->session_id !== session()->getId()) {
            abort(403, 'Accès non autorisé pour annuler ce ticket.');
        }

        // Marquer le ticket comme annulé au lieu de le supprimer
        $ticket->update([
            'status' => 'cancelled',
            'cancelled_at' => now()
        ]);

        // Diffuser un événement de mise à jour du statut du ticket
        event(new TicketStatusUpdated($ticket->id, $ticket->queue_id, 'cancelled'));

        return redirect()->route('public.queues.index')
            ->with('success', 'Votre ticket a été annulé avec succès.');
    }

    public function pauseTicket(Request $request, Ticket $ticket)
    {
        if ($ticket->session_id !== session()->getId()) {
            abort(403, 'Accès non autorisé pour mettre en pause ce ticket.');
        }

        // Vérifier que le ticket est en attente
        if ($ticket->status !== 'waiting') {
            return back()->with('error', 'Seuls les tickets en attente peuvent être marqués comme sortis momentanément.');
        }

        // Mettre à jour le statut du ticket
        $ticket->update([
            'status' => 'paused',
            'paused_at' => now(),
            'notify_when_close' => true // Activer les notifications pour ce ticket
        ]);

        // Diffuser un événement de mise à jour du statut du ticket
        event(new TicketStatusUpdated($ticket->id, $ticket->queue_id, 'paused'));

        return back()->with('success', 'Votre ticket a été marqué comme sorti momentanément. Votre position dans la file est préservée et vous serez notifié lorsque votre tour approchera.');
    }

    public function resumeTicket(Request $request, Ticket $ticket)
    {
        if ($ticket->session_id !== session()->getId()) {
            abort(403, 'Accès non autorisé pour réactiver ce ticket.');
        }

        // Vérifier que le ticket est en pause
        if ($ticket->status !== 'paused') {
            return back()->with('error', 'Seuls les tickets marqués comme sortis momentanément peuvent être réactivés.');
        }

        // Récupérer la file d'attente associée au ticket
        $queue = $ticket->queue;

        // Remettre le ticket en attente
        $ticket->update([
            'status' => 'waiting',
            'paused_at' => null,
            'notify_when_close' => false // Désactiver les notifications
        ]);

        // Diffuser un événement de mise à jour du statut du ticket
        event(new TicketStatusUpdated($ticket->id, $ticket->queue_id, 'waiting'));

        return redirect()->route('public.ticket.status', [
            'queue_code' => $queue->code,
            'ticket_code' => $ticket->code_ticket
        ])->with('success', 'Votre ticket est de nouveau actif dans la file d\'attente. Votre position a été préservée.');
    }

    public function showByCode($code)
    {
        $queue = \App\Models\Queue::where('code', $code)->firstOrFail();

        $sessionId = session()->getId();
        $ticket = $queue->tickets()
            ->where('session_id', $sessionId)
            ->whereIn('status', ['waiting', 'called'])
            ->first();

        if ($ticket) {
            return redirect()->route('public.ticket.status', ['queue_code' => $queue->code, 'ticket_code' => $ticket->code_ticket]);
        }

        $waitingTicketsCount = $queue->tickets()->where('status', 'waiting', 'paused')->count();
        return view('public.queues.show', compact('queue', 'waitingTicketsCount'));
    }

    public function find(Request $request)
    {
        $request->validate([
            'queue_code' => 'required|string|exists:queues,code',
        ]);

        $queueCode = $request->input('queue_code');

        return redirect()->route('public.queue.show.code', ['code' => $queueCode]);
    }
}
