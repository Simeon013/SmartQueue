<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\QueueEvent;
use App\Models\QueuePermission;
use App\Models\Ticket;
use App\Models\User;
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
        
        // Super admin et admin voient toutes les files
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            $queues = Queue::with('establishment')
                         ->latest()
                         ->paginate(10);
        } 
        // Les agents ne voient que les files pour lesquelles ils ont une permission
        else {
            $accessibleQueueIds = $user->queuePermissions()->pluck('queue_id');
            
            $queues = Queue::with('establishment')
                         ->whereIn('id', $accessibleQueueIds)
                         ->latest()
                         ->paginate(10);
            
            // Si l'agent n'a accès à aucune file, on le redirige avec un message
            if ($accessibleQueueIds->isEmpty()) {
                return redirect()->route('admin.dashboard')
                               ->with('info', 'Vous n\'avez accès à aucune file d\'attente pour le moment.');
            }
        }
        
        return view('admin.queues.index', compact('queues'));
    }

    /**
     * Vérifie si un utilisateur peut accéder à une file d'attente
     */
    protected function userCanAccessQueue(User $user, Queue $queue): bool
    {
        // Les super admins et admins ont accès à toutes les files
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }
        
        // Vérifier si l'utilisateur a une permission explicite pour cette file
        return $user->queuePermissions()
            ->where('queue_id', $queue->id)
            ->exists();
    }
    
    /**
     * Vérifie si un utilisateur peut gérer une file d'attente (créer/modifier/supprimer)
     */
    protected function userCanManageQueue(User $user, Queue $queue = null): bool
    {
        // Les super admins peuvent tout faire
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // Les admins peuvent gérer toutes les files
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Pour la création, vérifier si l'utilisateur est connecté
        if (!$queue) {
            return $user->exists;
        }
        
        // Vérifier les permissions spécifiques pour cette file
        return $queue->userCanManage($user);
    }
    
    public function create()
    {
        // $user = Auth::user();
        
        // // N'importe quel utilisateur connecté peut créer une file
        // if (!$user) {
        //     abort(403, 'Vous devez être connecté pour créer une file d\'attente.');
        // }

        $establishment = \App\Models\Establishment::first();
        return view('admin.queues.create', compact('establishment'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // 'establishment_id' => 'required|exists:establishments,id',
            'is_active' => 'boolean',
        ]);
        $validated['code'] = \Illuminate\Support\Str::random(6);
        $validated['is_active'] = $request->has('is_active');
        $validated['establishment_id'] = \App\Models\Establishment::first()->id;

        // Créer la file
        $queue = Queue::create($validated);
        
        // Donner les droits de propriétaire à l'utilisateur qui a créé la file
        if ($user) {
            $queue->grantPermissionTo($user, 'owner');
        }

        // Activer automatiquement le mode "tous les agents - gestion complète"
        $queue->permissions()->create([
            'user_id' => null, // null = tous les agents
            'permission_type' => 'manager',
            'granted_by' => $user->id,
        ]);

        return redirect()->route('admin.queues.permissions', $queue)
            ->with('success', 'File d\'attente créée avec succès. Le mode "tous les agents - gestion complète" a été activé par défaut.');
        // return redirect()->route('admin.queues.show', $queue)
        //     ->with('success', 'File d\'attente créée avec succès.');
    }

    public function show(Queue $queue)
    {
        $user = auth()->user();
        
        // Vérifier les permissions pour voir cette file d'attente spécifique
        if (!$this->userCanAccessQueue($user, $queue)) {
            abort(403, 'Vous n\'avez pas la permission d\'accéder à cette file d\'attente.');
        }

        // Charger les relations nécessaires
        $queue->load([
            'tickets' => function ($query) {
                $query->latest()->limit(50);
            }, 
            'events' => function ($query) {
                $query->latest()->limit(50);
            },
            'permissions.user'
        ]);

        // Calculer les statistiques
        $stats = [
            'total_tickets' => $queue->tickets()->count(),
            'active_tickets' => $queue->tickets()->where('status', 'waiting')->count(),
            'average_wait_time' => $queue->tickets()
                ->whereNotNull('served_at')
                ->whereNotNull('called_at')
                ->avg(DB::raw('TIMESTAMPDIFF(SECOND, called_at, served_at)')),
        ];

        // Récupérer les tickets avec pagination
        $tickets = $queue->tickets()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        // Vérifier les permissions de l'utilisateur actuel
        $canManageQueue = $user->can('queues.manage') || $user->isAdmin() || $user->isSuperAdmin();
        $canEditQueue = $user->can('queues.edit') || $user->isAdmin() || $user->isSuperAdmin();
        $canDeleteQueue = $user->can('queues.delete') || $user->isSuperAdmin();

        return view('admin.queues.show', [
            'queue' => $queue,
            'stats' => $stats,
            'tickets' => $tickets,
            'canManageQueue' => $canManageQueue,
            'canEditQueue' => $canEditQueue,
            'canDeleteQueue' => $canDeleteQueue,
        ]);
    }

    public function edit(Queue $queue)
    {
        $user = auth()->user();
        
        // Vérifier les permissions
        if (!$this->userCanManageQueue($user, $queue)) {
            abort(403, 'Vous n\'avez pas la permission de modifier cette file d\'attente.');
        }

        $establishment = Establishment::first();
        return view('admin.queues.edit', compact('queue', 'establishment'));
    }

    public function update(Request $request, \App\Models\Queue $queue)
    {
        // Vérifier les permissions pour modifier cette file d'attente
        if (!$this->userCanManageQueue(auth()->user(), $queue)) {
            abort(403, 'Vous n\'avez pas la permission de modifier cette file d\'attente.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // 'establishment_id' => 'required|exists:establishments,id',
            'is_active' => 'boolean',
        ]);
        $validated['is_active'] = $request->has('is_active');
        $queue->update($validated);
        return redirect()->route('admin.queues.index')
            ->with('success', 'File d\'attente mise à jour avec succès.');
    }

    public function destroy(Queue $queue)
    {
        $user = auth()->user();
        
        // Vérifier les permissions
        if (!$this->userCanManageQueue($user, $queue)) {
            abort(403, 'Vous n\'avez pas la permission de supprimer cette file d\'attente.');
        }
        
        // Vérifier si l'utilisateur est propriétaire (pour les agents)
        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$queue->userOwns($user)) {
            abort(403, 'Seul le propriétaire peut supprimer cette file d\'attente.');
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