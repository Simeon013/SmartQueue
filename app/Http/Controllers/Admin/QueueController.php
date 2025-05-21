<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QueueController extends Controller
{
    public function index()
    {
        $queues = Queue::withCount(['tickets', 'events'])->paginate(10);
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
            'status' => 'required|in:active,paused,closed',
            'settings' => 'nullable|array',
        ]);
        $queue = Queue::create($validated);
        return redirect()->route('admin.queues.show', $queue)
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
            'active_tickets' => $queue->activeTickets()->count(),
            'average_wait_time' => $queue->tickets()
                ->whereNotNull('served_at')
                ->whereNotNull('called_at')
                ->avg(DB::raw('TIMESTAMPDIFF(SECOND, called_at, served_at)')),
        ];

        return view('admin.queues.show', compact('queue', 'stats'));
    }

    public function edit(Queue $queue)
    {
        return view('admin.queues.edit', compact('queue'));
    }

    public function update(Request $request, Queue $queue)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,paused,closed',
            'settings' => 'nullable|array',
        ]);
        $queue->update($validated);
        return redirect()->route('admin.queues.show', $queue)
            ->with('success', 'File d\'attente mise à jour avec succès.');
    }

    public function destroy(Queue $queue)
    {
        $queue->delete();
        return redirect()->route('admin.queues.index')
            ->with('success', 'File d\'attente supprimée avec succès.');
    }
}
