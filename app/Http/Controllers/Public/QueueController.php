<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class QueueController extends Controller
{
    public function show(Queue $queue)
    {
        $sessionId = session()->getId();
        $ticket = $queue->tickets()
            ->where('session_id', $sessionId)
            ->whereIn('status', ['waiting', 'called'])
            ->first();

        if ($ticket) {
            return redirect()->route('public.ticket.status', ['queue_code' => $queue->code, 'ticket_code' => $ticket->code_ticket]);
        }

        $waitingTicketsCount = $queue->tickets()->where('status', 'waiting')->count();
        return view('public.queues.show', compact('queue', 'waitingTicketsCount'));
    }

    public function join(Request $request, Queue $queue)
    {
        $sessionId = $request->session()->getId();
        $existingTicket = $queue->tickets()
            ->where('session_id', $sessionId)
            ->whereIn('status', ['waiting', 'called'])
            ->first();

        if ($existingTicket) {
            return redirect()->route('public.ticket.status', ['queue_code' => $queue->code, 'ticket_code' => $existingTicket->code_ticket])
                ->with('error', 'Vous avez déjà un ticket en cours pour cette file.');
        }

        $lastTicket = $queue->tickets()->orderByDesc('id')->first();
        $codeTicket = !$lastTicket ? 'A-01' : (function() use ($lastTicket) {
            if (preg_match('/^([A-Z])-(\d+)$/', $lastTicket->code_ticket, $matches)) {
                $letter = $matches[1];
                $number = (int)$matches[2];
                if ($number >= 99) {
                    $letter = chr(ord($letter) + 1);
                    $number = 1;
                } else {
                    $number++;
                }
                return sprintf('%s-%02d', $letter, $number);
            }
            return 'A-01';
        })();

        $ticket = $queue->tickets()->create([
            'queue_id' => $queue->id,
            'code_ticket' => $codeTicket,
            'status' => 'waiting',
            'session_id' => $sessionId,
        ]);

        return redirect()->route('public.ticket.status', ['queue_code' => $queue->code, 'ticket_code' => $ticket->code_ticket])
            ->with('success', 'Votre ticket ' . $ticket->code_ticket . ' a été créé avec succès !');
    }

    public function ticketStatus($queue_code, $ticket_code)
    {
        $queue = Queue::where('code', $queue_code)->firstOrFail();
        $ticket = Ticket::where('code_ticket', $ticket_code)->where('queue_id', $queue->id)->firstOrFail();

        if ($ticket->session_id !== session()->getId()) {
            abort(403, 'Accès non autorisé au ticket.');
        }

        return view('public.tickets.status', compact('queue', 'ticket'));
    }

    public function cancelTicket(Ticket $ticket)
    {
        // Ensure the ticket belongs to the current session for security
        if ($ticket->session_id !== session()->getId()) {
            abort(403, 'Accès non autorisé pour annuler ce ticket.');
        }

        $ticket->delete();

        return redirect()->route('public.queues.index')
            ->with('success', 'Votre ticket a été annulé avec succès.');
    }

    public function pauseTicket(Ticket $ticket)
    {
        // Ensure the ticket belongs to the current session for security
        if ($ticket->session_id !== session()->getId()) {
            abort(403, 'Accès non autorisé pour mettre en pause ce ticket.');
        }

        // Update the ticket status to 'paused'
        $ticket->update(['status' => 'paused']);

        // Re-fetch data and return the current view
        $queue = $ticket->queue;
        $currentServingTicket = $queue->tickets()->where('status', 'called')->orderBy('updated_at', 'desc')->first();
        $currentServingTicketCode = $currentServingTicket ? $currentServingTicket->code_ticket : 'Aucun ticket en cours de traitement';
        $currentServingNumber = $currentServingTicket ? $currentServingTicket->number : 'N/A';
        $waitingTicketsCount = $queue->tickets()->where('status', 'waiting')->count();
        $position = $ticket->getPositionAttribute();
        $estimatedWaitTime = $ticket->getEstimatedWaitTimeAttribute();

        return view('public.tickets.status', compact('queue', 'ticket', 'position', 'estimatedWaitTime', 'currentServingNumber', 'currentServingTicketCode', 'waitingTicketsCount'))
            ->with('success', 'Votre ticket a été mis en pause momentanément.');
    }

    public function resumeTicket(Ticket $ticket)
    {
        // Ensure the ticket belongs to the current session for security
        if ($ticket->session_id !== session()->getId()) {
            abort(403, 'Accès non autorisé pour reprendre ce ticket.');
        }

        // Update the ticket status to 'waiting'
        $ticket->update(['status' => 'waiting']);

        // Re-fetch data and return the current view
        $queue = $ticket->queue;
        $currentServingTicket = $queue->tickets()->where('status', 'called')->orderBy('updated_at', 'desc')->first();
        $currentServingTicketCode = $currentServingTicket ? $currentServingTicket->code_ticket : 'Aucun ticket en cours de traitement';
        $currentServingNumber = $currentServingTicket ? $currentServingTicket->number : 'N/A';
        $waitingTicketsCount = $queue->tickets()->where('status', 'waiting')->count();
        $position = $ticket->getPositionAttribute();
        $estimatedWaitTime = $ticket->getEstimatedWaitTimeAttribute();

        return view('public.tickets.status', compact('queue', 'ticket', 'position', 'estimatedWaitTime', 'currentServingNumber', 'currentServingTicketCode', 'waitingTicketsCount'))
            ->with('success', 'Votre ticket est de nouveau actif dans la file.');
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

        $waitingTicketsCount = $queue->tickets()->where('status', 'waiting')->count();
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
