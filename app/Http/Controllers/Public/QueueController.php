<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QueueController extends Controller
{
    public function show(Queue $queue)
    {
        return view('public.queue.show', compact('queue'));
    }

    public function join(Request $request, Queue $queue)
    {
        // Vérifier si le client a déjà un ticket actif
        $sessionId = $request->session()->getId();
        $existingTicket = $queue->tickets()
            ->where('session_id', $sessionId)
            ->whereIn('status', ['waiting', 'called'])
            ->first();

        if ($existingTicket) {
            return redirect()->route('public.queue.show', $queue)
                ->with('error', 'Vous avez déjà un ticket en cours.');
        }

        // Générer un nouveau numéro de ticket
        $lastTicket = $queue->tickets()->latest()->first();
        $nextNumber = $lastTicket ? $lastTicket->number + 1 : 1;

        // Créer le ticket
        $ticket = $queue->tickets()->create([
            'number' => $nextNumber,
            'status' => 'waiting',
            'session_id' => $sessionId,
        ]);

        return redirect()->route('public.queue.show', $queue)
            ->with('success', 'Votre ticket a été créé avec succès.');
    }

    public function status(Queue $queue)
    {
        $ticket = $queue->tickets()
            ->where('session_id', request()->session()->getId())
            ->whereIn('status', ['waiting', 'called'])
            ->first();

        return response()->json([
            'ticket' => $ticket,
            'queue_status' => $queue->status,
            'position' => $ticket ? $queue->tickets()
                ->where('status', 'waiting')
                ->where('id', '<', $ticket->id)
                ->count() + 1 : null,
        ]);
    }

    public function showByCode($code)
    {
        $queue = \App\Models\Queue::where('code', $code)->firstOrFail();
        return view('public.queue.show', compact('queue'));
    }
}
