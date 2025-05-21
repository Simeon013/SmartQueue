<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\Ticket;
use App\Models\QueueEvent;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function dashboard(Queue $queue)
    {
        $activeTickets = $queue->activeTickets()
            ->orderBy('created_at')
            ->get();

        return view('agent.queue.dashboard', compact('queue', 'activeTickets'));
    }

    public function getTickets(Queue $queue)
    {
        $currentTicket = $queue->tickets()
            ->where('status', 'called')
            ->latest()
            ->first();

        $waitingTickets = $queue->tickets()
            ->where('status', 'waiting')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'currentTicket' => $currentTicket,
            'waitingTickets' => $waitingTickets,
        ]);
    }

    public function callNext(Request $request, Queue $queue)
    {
        $nextTicket = $queue->tickets()
            ->where('status', 'waiting')
            ->orderBy('created_at')
            ->first();

        if (!$nextTicket) {
            return response()->json(['message' => 'Aucun ticket en attente']);
        }

        $nextTicket->update([
            'status' => 'called',
            'called_at' => now(),
        ]);

        QueueEvent::create([
            'queue_id' => $queue->id,
            'ticket_id' => $nextTicket->id,
            'event_type' => 'called',
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'ticket' => $nextTicket,
            'message' => 'Ticket appelé avec succès'
        ]);
    }

    public function markPresent(Request $request, Queue $queue, Ticket $ticket)
    {
        if ($ticket->status !== 'called') {
            return response()->json(['message' => 'Ce ticket n\'a pas été appelé'], 400);
        }

        $ticket->update([
            'status' => 'served',
            'served_at' => now(),
        ]);

        QueueEvent::create([
            'queue_id' => $queue->id,
            'ticket_id' => $ticket->id,
            'event_type' => 'served',
            'user_id' => auth()->id(),
        ]);

        return response()->json(['message' => 'Présence confirmée']);
    }

    public function skip(Request $request, Queue $queue, Ticket $ticket)
    {
        if ($ticket->status !== 'called') {
            return response()->json(['message' => 'Ce ticket n\'a pas été appelé'], 400);
        }

        $ticket->update(['status' => 'skipped']);

        QueueEvent::create([
            'queue_id' => $queue->id,
            'ticket_id' => $ticket->id,
            'event_type' => 'skipped',
            'user_id' => auth()->id(),
        ]);

        return response()->json(['message' => 'Ticket passé']);
    }
}