<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Queue $queue)
    {
        $tickets = $queue->tickets()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.queues.tickets.index', compact('queue', 'tickets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Queue $queue)
    {
        return view('admin.queues.tickets.create', compact('queue'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Queue $queue)
    {
        // Plus de validation sur name, email, phone
        $validated = $request->validate([
            'wants_notifications' => 'boolean',
            'notification_channel' => 'required_if:wants_notifications,true|in:email,sms',
        ]);

        $lastTicket = $queue->tickets()->latest()->first();
        $number = $lastTicket ? $lastTicket->number + 1 : 1;

        $ticket = $queue->tickets()->create([
            'number' => $number,
            'status' => 'waiting',
            'wants_notifications' => $validated['wants_notifications'] ?? false,
            'notification_channel' => $validated['notification_channel'] ?? null,
        ]);

        return redirect()->route('admin.queues.tickets.index', $queue)
            ->with('success', 'Ticket créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Queue $queue, Ticket $ticket)
    {
        if ($ticket->queue_id !== $queue->id) {
            abort(404);
        }

        return view('admin.queues.tickets.edit', compact('queue', 'ticket'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Queue $queue, Ticket $ticket)
    {
        if ($ticket->queue_id !== $queue->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => 'required|in:waiting,called,served,skipped',
            'wants_notifications' => 'boolean',
            'notification_channel' => 'required_if:wants_notifications,true|in:email,sms',
        ]);

        $ticket->update($validated);

        if ($validated['status'] === 'called' && !$ticket->called_at) {
            $ticket->update(['called_at' => now()]);
        } elseif ($validated['status'] === 'served' && !$ticket->served_at) {
            $ticket->update(['served_at' => now()]);
        }

        return redirect()->route('admin.queues.tickets.index', $queue)
            ->with('success', 'Ticket mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Queue $queue, Ticket $ticket)
    {
        if ($ticket->queue_id !== $queue->id) {
            abort(404);
        }

        $ticket->delete();

        return redirect()->route('admin.queues.tickets.index', $queue)
            ->with('success', 'Ticket supprimé avec succès.');
    }

    public function updateStatus(Request $request, Queue $queue, Ticket $ticket)
    {
        if ($ticket->queue_id !== $queue->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => 'required|in:waiting,called,served,skipped',
        ]);

        $ticket->update($validated);

        // Mettre à jour les timestamps si nécessaire
        if ($validated['status'] === 'called' && !$ticket->called_at) {
            $ticket->update(['called_at' => now()]);
        } elseif ($validated['status'] === 'served' && !$ticket->served_at) {
            $ticket->update(['served_at' => now()]);
        }

        return redirect()->route('admin.queues.tickets.index', $queue)
            ->with('success', 'Statut du ticket mis à jour avec succès.');
    }
}
