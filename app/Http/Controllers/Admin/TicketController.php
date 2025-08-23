<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Queue $queue)
    {
        // Vérifier les permissions pour voir les tickets de cette file d'attente
        if (!Auth::user()->can('view', $queue)) {
            abort(403, 'Vous n\'avez pas la permission de voir les tickets de cette file d\'attente.');
        }

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
        // Vérifier les permissions pour créer des tickets dans cette file d'attente
        if (!Auth::user()->can('manage_tickets', $queue)) {
            abort(403, 'Vous n\'avez pas la permission de créer des tickets dans cette file d\'attente.');
        }

        return view('admin.queues.tickets.create', compact('queue'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Queue $queue)
    {
        // Vérifier les permissions pour créer des tickets dans cette file d'attente
        if (!Auth::user()->can('manage_tickets', $queue)) {
            abort(403, 'Vous n\'avez pas la permission de créer des tickets dans cette file d\'attente.');
        }

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
        // Vérifier les permissions pour modifier des tickets dans cette file d'attente
        if (!Auth::user()->can('manage_tickets', $queue)) {
            abort(403, 'Vous n\'avez pas la permission de modifier des tickets dans cette file d\'attente.');
        }

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
        // Vérifier les permissions pour modifier des tickets dans cette file d'attente
        if (!Auth::user()->can('manage_tickets', $queue)) {
            abort(403, 'Vous n\'avez pas la permission de modifier des tickets dans cette file d\'attente.');
        }

        if ($ticket->queue_id !== $queue->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => 'required|in:waiting,paused,in_progress,served,skipped',
            'wants_notifications' => 'boolean',
            'notification_channel' => 'required_if:wants_notifications,true|in:email,sms',
        ]);

        $user = Auth::user();
        $status = $validated['status'];

        // Mise à jour du statut avec gestion multi-agents
        if ($status === 'in_progress') {
            if ($ticket->is_being_handled && !$ticket->isHandledBy($user)) {
                return back()->with('error', 'Ce ticket est déjà en cours de traitement par un autre agent.');
            }

            $ticket->assignTo($user);
        } elseif ($status === 'served') {
            $ticket->markAsServed();
        } elseif ($status === 'skipped') {
            $ticket->markAsSkipped();
        } else {
            // Remise en attente
            $ticket->release();
        }

        return redirect()->route('admin.queues.tickets.index', $queue)
            ->with('success', 'Statut du ticket mis à jour avec succès.');
    }

    /**
     * Marque le ticket comme annulé au lieu de le supprimer.
     */
    public function destroy(Queue $queue, Ticket $ticket)
    {
        // Vérifier les permissions pour gérer les tickets dans cette file d'attente
        if (!Auth::user()->can('manage_tickets', $queue)) {
            abort(403, 'Vous n\'avez pas la permission de gérer les tickets dans cette file d\'attente.');
        }

        if ($ticket->queue_id !== $queue->id) {
            abort(404);
        }

        // Marquer le ticket comme annulé au lieu de le supprimer
        $ticket->update([
            'status' => 'cancelled',
            'handled_at' => now(),
            'handled_by' => Auth::id()
        ]);

        return redirect()->route('admin.queues.tickets.index', $queue)
            ->with('success', 'Le ticket a été marqué comme annulé avec succès.');
    }

    public function updateStatus(Request $request, Queue $queue, Ticket $ticket)
    {
        // Vérifier les permissions pour gérer les tickets dans cette file d'attente
        if (!Auth::user()->can('manage_tickets', $queue)) {
            abort(403, 'Vous n\'avez pas la permission de gérer les tickets dans cette file d\'attente.');
        }

        if ($ticket->queue_id !== $queue->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => 'required|in:waiting,paused,ining,in_progress,served,skipped',
        ]);

        $user = Auth::user();
        $status = $validated['status'];

        // Mise à jour du statut avec gestion multi-agents
        if ($status === 'in_progress') {
            if ($ticket->is_being_handled && !$ticket->isHandledBy($user)) {
                return back()->with('error', 'Ce ticket est déjà en cours de traitement par un autre agent.');
            }

            $ticket->assignTo($user);
        } elseif ($status === 'served') {
            $ticket->markAsServed();
        } elseif ($status === 'skipped') {
            $ticket->markAsSkipped();
        } else {
            // Remise en attente
            $ticket->release();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Statut du ticket mis à jour avec succès.',
                'ticket' => $ticket->fresh()
            ]);
        }

        return redirect()->route('admin.queues.tickets.index', $queue)
            ->with('success', 'Statut du ticket mis à jour avec succès.');
    }
}
