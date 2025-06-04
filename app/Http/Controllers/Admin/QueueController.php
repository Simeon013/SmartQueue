<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QueueController extends Controller
{
    public function index()
    {
        $queues = Queue::latest()->paginate(10);
        return view('admin.queues.index', compact('queues'));
    }

    public function create()
    {
        return view('admin.queues.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['code'] = Str::random(6);
        $validated['is_active'] = $request->has('is_active');

        Queue::create($validated);

        return redirect()->route('admin.queues.index')
            ->with('success', 'File d\'attente créée avec succès.');
    }

    public function show(Queue $queue)
    {
        $queue->load(['tickets' => function ($query) {
            $query->latest()->limit(50);
        }, 'events' => function ($query) {
            $query->latest()->limit(50);
        }]);

        $stats = [
            'total_tickets' => $queue->tickets()->count(),
            'active_tickets' => $queue->tickets()->where('status', 'waiting')->count(),
            'average_wait_time' => $queue->tickets()
                ->whereNotNull('served_at')
                ->whereNotNull('called_at')
                ->avg(DB::raw('TIMESTAMPDIFF(SECOND, called_at, served_at)')),
        ];

        $tickets = $queue->tickets()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.queues.show', compact('queue', 'stats', 'tickets'));
    }

    public function edit(Queue $queue)
    {
        return view('admin.queues.edit', compact('queue'));
    }

    public function update(Request $request, Queue $queue)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $queue->update($validated);

        return redirect()->route('admin.queues.index')
            ->with('success', 'File d\'attente mise à jour avec succès.');
    }

    public function destroy(Queue $queue)
    {
        $queue->delete();

        return redirect()->route('admin.queues.index')
            ->with('success', 'File d\'attente supprimée avec succès.');
    }

    public function addTicket(Request $request, Queue $queue)
    {
        $lastTicket = $queue->tickets()->latest()->first();
        $number = $lastTicket ? $lastTicket->number + 1 : 1;

        $ticket = $queue->tickets()->create([
            'number' => $number,
            'status' => 'waiting',
            'session_id' => session()->getId(),
        ]);

        return redirect()->route('admin.queues.show', $queue)
            ->with('success', 'Ticket ajouté avec succès.');
    }

    public function destroyTicket(Queue $queue, Ticket $ticket)
    {
        if ($ticket->queue_id !== $queue->id) {
            abort(404);
        }

        $ticket->delete();

        return redirect()->route('admin.queues.show', $queue)
            ->with('success', 'Ticket supprimé avec succès.');
    }

    public function updateTicketStatus(Request $request, Queue $queue, Ticket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|in:waiting,called,served,skipped',
        ]);

        $ticket->update($validated);

        if ($validated['status'] === 'called') {
            $ticket->update(['called_at' => now()]);
        } elseif ($validated['status'] === 'served') {
            $ticket->update(['served_at' => now()]);
        }

        return redirect()->route('admin.queues.show', $queue)
            ->with('success', 'Statut du ticket mis à jour avec succès.');
    }

    public function updateTicket(Request $request, Queue $queue, Ticket $ticket)
    {
        $validated = $request->validate([
            'number' => 'required|integer|min:1',
            'status' => 'required|in:waiting,called,served,skipped',
            'notes' => 'nullable|string',
        ]);

        $ticket->update($validated);

        return redirect()->route('admin.queues.show', $queue)
            ->with('success', 'Ticket mis à jour avec succès.');
    }
}
