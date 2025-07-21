<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\Ticket;
use App\Models\Establishment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QueueController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // On laisse l'accès à tous les utilisateurs authentifiés, la liste sera filtrée selon les permissions
        if ($user->can('queues.view_all') || $user->hasRole('super-admin')) {
            $queues = Queue::latest()->paginate(10);
        } else {
            $accessibleQueueIds = $user->getAccessibleQueueIds();
            $queues = Queue::whereIn('id', $accessibleQueueIds)->latest()->paginate(10);
        }
        return view('admin.queues.index', compact('queues'));
    }

    public function create()
    {
        // Vérifier les permissions pour créer des files d'attente
        if (!Auth::user()->can('queues.create')) {
            abort(403, 'Vous n\'avez pas la permission de créer des files d\'attente.');
        }

        $establishment = \App\Models\Establishment::first();
        return view('admin.queues.create', compact('establishment'));
    }

    public function store(Request $request)
    {
        // Vérifier les permissions pour créer des files d'attente
        if (!auth()->user()->can('queues.create')) {
            abort(403, 'Vous n\'avez pas la permission de créer des files d\'attente.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // 'establishment_id' => 'required|exists:establishments,id',
            'is_active' => 'boolean',
        ]);
        $validated['code'] = \Illuminate\Support\Str::random(6);
        $validated['is_active'] = $request->has('is_active');
        $validated['establishment_id'] = \App\Models\Establishment::first()->id;

        $queue = \App\Models\Queue::create($validated);

        // Donner automatiquement les permissions à l'utilisateur qui crée la file d'attente
        $user = auth()->user();
        $user->grantQueuePermission($queue, 'owner');

        // Activer automatiquement le mode "tous les agents - gestion complète"
        $queue->permissions()->create([
            'user_id' => null, // null = tous les agents
            'permission_type' => 'manager',
            'granted_by' => $user->id,
        ]);

        return redirect()->route('admin.queues.permissions', $queue)
            ->with('success', 'File d\'attente créée avec succès. Le mode "tous les agents - gestion complète" a été activé par défaut.');
    }

    public function show(Queue $queue)
    {
        // Vérifier les permissions pour voir cette file d'attente spécifique
        if (!auth()->user()->can('queues.view')) {
            abort(403, 'Vous n\'avez pas la permission de voir cette file d\'attente.');
        }

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

    public function edit(\App\Models\Queue $queue)
    {
        // Vérifier les permissions pour modifier cette file d'attente
        if (!$queue->userCanManage(auth()->user()) && !auth()->user()->hasRole('super-admin')) {
            abort(403, 'Vous n\'avez pas la permission de modifier cette file d\'attente.');
        }

        $establishment = \App\Models\Establishment::first();
        return view('admin.queues.edit', compact('queue', 'establishment'));
    }

    public function update(Request $request, \App\Models\Queue $queue)
    {
        // Vérifier les permissions pour modifier cette file d'attente
        if (!$queue->userCanManage(auth()->user()) && !auth()->user()->hasRole('super-admin')) {
            abort(403, 'Vous n\'avez pas la permission de modifier cette file d\'attente.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // 'establishment_id' => 'required|exists:establishments,id',
            'is_active' => 'boolean',
        ]);
        $validated['is_active'] = $request->has('is_active');
        $validated['establishment_id'] = \App\Models\Establishment::first()->id;
        $queue->update($validated);
        return redirect()->route('admin.queues.index')
            ->with('success', 'File d\'attente mise à jour avec succès.');
    }

    public function destroy(Queue $queue)
    {
        // Vérifier les permissions pour supprimer cette file d'attente
        if (!auth()->user()->ownsQueue($queue) && !auth()->user()->hasRole('super-admin')) {
            abort(403, 'Vous n\'avez pas la permission de supprimer cette file d\'attente.');
        }

        $queue->delete();

        return redirect()->route('admin.queues.index')
            ->with('success', 'File d\'attente supprimée avec succès.');
    }

    public function addTicket(Request $request, Queue $queue)
    {
        // Vérifier les permissions pour ajouter des tickets à cette file d'attente
        if (!$queue->userCanOperate(auth()->user()) && !auth()->user()->hasRole('super-admin')) {
            abort(403, 'Vous n\'avez pas la permission d\'ajouter des tickets à cette file d\'attente.');
        }

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
        // Vérifier les permissions pour supprimer des tickets de cette file d'attente
        if (!$queue->userCanOperate(auth()->user()) && !auth()->user()->hasRole('super-admin')) {
            abort(403, 'Vous n\'avez pas la permission de supprimer des tickets de cette file d\'attente.');
        }

        if ($ticket->queue_id !== $queue->id) {
            abort(404);
        }

        $ticket->delete();

        return redirect()->route('admin.queues.show', $queue)
            ->with('success', 'Ticket supprimé avec succès.');
    }

    public function updateTicketStatus(Request $request, Queue $queue, Ticket $ticket)
    {
        // Vérifier les permissions pour gérer les tickets de cette file d'attente
        if (!$queue->userCanOperate(auth()->user()) && !auth()->user()->hasRole('super-admin')) {
            abort(403, 'Vous n\'avez pas la permission de gérer les tickets de cette file d\'attente.');
        }

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
        // Vérifier les permissions pour gérer les tickets de cette file d'attente
        if (!$queue->userCanOperate(auth()->user()) && !auth()->user()->hasRole('super-admin')) {
            abort(403, 'Vous n\'avez pas la permission de gérer les tickets de cette file d\'attente.');
        }

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
