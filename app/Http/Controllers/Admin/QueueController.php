<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Facades\Activity;
use App\Models\Queue;
use App\Models\QueueEvent;
use App\Models\QueuePermission;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Establishment;
use App\Enums\QueueStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class QueueController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Initialisation de la requête
        $query = Queue::with(['establishment', 'permissions']);

        // Filtres communs à tous les utilisateurs
        if ($request->filled('name')) {
            $searchTerm = '%' . $request->name . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('queues.name', 'like', $searchTerm)
                  ->orWhereHas('creator', function($q) use ($searchTerm) {
                      $q->where('name', 'like', $searchTerm);
                  });
            });
        }

        // Filtre par statut
        if ($request->has('status')) {
            if (in_array($request->status, ['0', '1'])) {
                // Rétrocompatibilité avec les anciens appels utilisant 0/1
                $query->where('status', $request->status === '1' ? 'open' : 'closed');
            } elseif (in_array($request->status, ['open', 'paused', 'blocked', 'closed'])) {
                $query->where('status', $request->status);
            }
        }

        // Compter les tickets en attente pour chaque file
        $query->withCount(['tickets' => function($q) {
            $q->where('status', 'waiting');
        }]);

        // Filtrage spécifique aux administrateurs
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            // Les administrateurs voient toutes les files, pas de restriction supplémentaire
        }
        // Filtrage spécifique aux agents
        else {
            // Récupérer les IDs des files accessibles en fonction des permissions
            $accessibleQueueIds = $user->getAccessibleQueueIds();

            // Si un filtre de permission est spécifié, on l'applique
            if ($request->filled('permission')) {
                $filteredQueueIds = [];

                // Récupérer les permissions spécifiques
                $permissionsQuery = $user->queuePermissions()
                    ->whereIn('queue_id', $accessibleQueueIds);

                if ($request->permission === 'manage') {
                    $permissionsQuery->whereIn('permission_type', ['owner', 'manager']);
                } elseif ($request->permission === 'view') {
                    $permissionsQuery->where('permission_type', 'operator');
                }

                $specificPermissions = $permissionsQuery->pluck('queue_id')->toArray();

                // Récupérer les permissions globales si nécessaire
                $globalPermissions = [];
                if (empty($specificPermissions) || $request->permission === 'view') {
                    $globalQuery = DB::table('queue_permissions')
                        ->whereNull('user_id')
                        ->whereIn('queue_id', $accessibleQueueIds);

                    if ($request->permission === 'manage') {
                        $globalQuery->whereIn('permission_type', ['owner', 'manager']);
                    } elseif ($request->permission === 'view') {
                        $globalQuery->where('permission_type', 'operator');
                    }

                    $globalPermissions = $globalQuery->pluck('queue_id')->toArray();
                }

                // Fusionner les permissions spécifiques et globales
                $filteredQueueIds = array_unique(array_merge($specificPermissions, $globalPermissions));

                // Si on a des permissions filtrées, on les utilise
                if (!empty($filteredQueueIds)) {
                    $accessibleQueueIds = $filteredQueueIds;
                }
            }

            // Appliquer le filtre sur les IDs accessibles
            $query->whereIn('id', $accessibleQueueIds);
        }

        // Gestion du tri
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        switch ($sort) {
            case 'name':
                $query->orderBy('name', $direction);
                break;
            case 'status':
                $query->orderBy('status', $direction);
                break;
            case 'tickets_count':
                $query->orderBy('tickets_count', $direction);
                break;
            case 'creator_name':
                $query->leftJoin('users', 'queues.created_by', '=', 'users.id')
                      ->orderBy('users.name', $direction)
                      ->select('queues.*');
                break;
            case 'created_at':
            default:
                $query->orderBy('created_at', $direction);
                break;
        }

        $queues = $query->paginate(10)->withQueryString();

        // Statistiques pour le récapitulatif
        $stats = [
            'total_queues' => Queue::count(),
            'active_queues' => Queue::where('status', 'open')->count(),
            'total_pending_tickets' => Ticket::where('status', 'waiting')->count(),
            'latest_queue' => Queue::with('creator')->latest()->first()
        ];

        return view('admin.queues.index', [
            'queues' => $queues,
            'stats' => $stats
        ]);
    }

    /**
     * Vérifie si un utilisateur peut accéder à une file d'attente
     * Prend en compte les permissions globales (user_id = null) et spécifiques
     */
    protected function userCanAccessQueue(User $user, Queue $queue): bool
    {
        // Les super admins et admins ont accès à toutes les files
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        // Vérifier les permissions globales (user_id = null)
        $hasGlobalPermission = DB::table('queue_permissions')
            ->where('queue_id', $queue->id)
            ->whereNull('user_id')
            ->exists();

        if ($hasGlobalPermission) {
            return true;
        }

        // Vérifier les permissions spécifiques à l'utilisateur
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
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Les admins peuvent gérer toutes les files
        if ($user->isAdmin()) {
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
        $establishment = \App\Models\Establishment::first();
        $services = \App\Models\Service::where('is_active', true)
            ->orderBy('position')
            ->orderBy('name')
            ->get();
            
        return view('admin.queues.create', compact('establishment', 'services'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            // Le nom est généré automatiquement maintenant
        ]);

        // Récupérer le service sélectionné
        $service = \App\Models\Service::findOrFail($validated['service_id']);
        
        // Générer le nom automatiquement
        $date = now()->format('Y-m-d');
        $time = now()->format('H:i');
        $queueName = "{$service->name} - {$date} {$time}";

        // Préparer les données pour la création
        $queueData = [
            'name' => $queueName,
            'service_id' => $service->id,
            'code' => \Illuminate\Support\Str::random(6),
            'status' => QueueStatus::OPEN->value,
            'establishment_id' => \App\Models\Establishment::first()->id,
            'created_by' => $user->id,
        ];

        // Créer la file
        $queue = Queue::create($queueData);

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
        $user = auth()->user();

        // Vérifier les permissions pour modifier cette file d'attente
        if (!$this->userCanManageQueue($user, $queue)) {
            abort(403, 'Vous n\'avez pas la permission de modifier cette file d\'attente.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:' . implode(',', array_column(QueueStatus::cases(), 'value')),
        ]);

        $newStatus = QueueStatus::from($validated['status']);

        // Si la file est fermée, on ne peut pas la modifier
        if ($queue->status === QueueStatus::CLOSED) {
            return redirect()->back()
                ->with('error', 'Impossible de modifier une file fermée.');
        }

        // Si le statut a changé
        if ($newStatus !== $queue->status) {
            // Journaliser le changement de statut
            // try {
            //     Activity::onQueue('default')
            //         ->performedOn($queue)
            //         ->withProperties([
            //             'old_status' => $queue->status->value,
            //             'new_status' => $newStatus->value,
            //             'changed_by' => $user->id
            //         ])
            //         ->log('Changement de statut de la file');
            // } catch (\Exception $e) {
            //     // En cas d'erreur avec le logging, on continue quand même
            //     \Log::error('Erreur lors du journal du changement de statut: ' . $e->getMessage());
            // }

            // Si la file est bloquée, on annule tous les tickets en attente
            if ($newStatus === QueueStatus::BLOCKED) {
                $queue->tickets()
                    ->where('status', 'waiting')
                    ->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                        'cancelled_by' => $user->id,
                        'cancellation_reason' => 'File bloquée par un administrateur'
                    ]);
            }

            // Si la file est fermée, on annule tous les tickets en attente
            if ($newStatus === QueueStatus::CLOSED) {
                $queue->tickets()
                    ->where('status', 'waiting')
                    ->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                        'cancelled_by' => $user->id,
                        'cancellation_reason' => 'File fermée par un administrateur'
                    ]);
            }
        }

        // Mettre à jour uniquement le nom, le statut sera géré séparément
        $queue->update([
            'name' => $validated['name'],
            'status' => $newStatus
        ]);

        return redirect()->route('admin.queues.show', $queue)
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
        if (!$queue->userCanOperate(auth()->user()) && !auth()->user()->isSuperAdmin()) {
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
        if ($ticket->queue_id !== $queue->id) {
            abort(404);
        }

        // Marquer le ticket comme annulé au lieu de le supprimer
        $ticket->update([
            'status' => 'cancelled',
            'handled_at' => now(),
            'handled_by' => Auth::id()
        ]);

        return redirect()->route('admin.queues.show', $queue)
            ->with('success', 'Le ticket a été marqué comme annulé avec succès.');
    }

    public function updateTicketStatus(Request $request, Queue $queue, Ticket $ticket)
    {
        // Vérifier les permissions pour gérer les tickets de cette file d'attente
        if (!$queue->userCanOperate(auth()->user()) && !auth()->user()->isSuperAdmin()) {
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
        if (!$queue->userCanOperate(auth()->user()) && !auth()->user()->isSuperAdmin()) {
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

    /**
     * Bascule le statut actif/désactivé d'une file d'attente
     *
     * @param  \App\Models\Queue  $queue
     * @return \Illuminate\Http\Response
     */
    public function toggleStatus(Queue $queue)
    {
        $user = auth()->user();

        // Vérifier les permissions
        if (!$this->userCanManageQueue($user, $queue)) {
            abort(403, 'Vous n\'avez pas la permission de modifier cette file d\'attente.');
        }

        // Si la file est fermée, on ne peut pas changer son statut
        if ($queue->status === QueueStatus::CLOSED) {
            return redirect()->back()
                ->with('error', 'Impossible de modifier le statut d\'une file fermée.');
        }

        // Basculer entre ouvert et bloqué
        $newStatus = $queue->status === QueueStatus::OPEN
            ? QueueStatus::BLOCKED
            : QueueStatus::OPEN;

        $queue->update(['status' => $newStatus]);

        $status = $newStatus === QueueStatus::OPEN ? 'débloquée' : 'bloquée';
        return redirect()->back()
            ->with('success', "La file a été {$status} avec succès.");
    }

    /**
     * Bascule l'état de pause d'une file d'attente
     *
     * @param  \App\Models\Queue  $queue
     * @return \Illuminate\Http\Response
     */
    public function togglePause(Queue $queue)
    {
        $user = auth()->user();

        // Vérifier les permissions
        if (!$this->userCanManageQueue($user, $queue)) {
            abort(403, 'Vous n\'avez pas la permission de modifier cette file d\'attente.');
        }

        // Si la file est en pause, on peut la reprendre
        if ($queue->status === QueueStatus::PAUSED) {
            $newStatus = QueueStatus::OPEN;
        } 
        // Sinon, on vérifie qu'elle est bien ouverte avant de la mettre en pause
        else if ($queue->status === QueueStatus::OPEN) {
            $newStatus = QueueStatus::PAUSED;
        } else {
            return redirect()->back()
                ->with('error', 'Impossible de modifier l\'état de cette file.');
        }

        $queue->update(['status' => $newStatus]);

        $status = $newStatus === QueueStatus::PAUSED ? 'mise en pause' : 'reprise';
        return redirect()->back()
            ->with('success', "La file a été {$status} avec succès.");
    }

    /**
     * Ferme une file d'attente et annule les tickets en attente
     *
     * @param  \App\Models\Queue  $queue
     * @return \Illuminate\Http\Response
     */
    public function close(Queue $queue)
    {
        $user = auth()->user();

        // Vérifier les permissions
        if (!$this->userCanManageQueue($user, $queue)) {
            abort(403, 'Vous n\'avez pas la permission de fermer cette file d\'attente.');
        }

        // Si la file est déjà fermée, on ne fait rien
        if ($queue->status === QueueStatus::CLOSED) {
            return redirect()->back()
                ->with('warning', 'Cette file est déjà fermée.');
        }

        // Fermer la file
        $queue->update(['status' => QueueStatus::CLOSED]);

        // Annuler les tickets en attente
        $queue->tickets()
            ->whereIn('status', ['waiting', 'called'])
            ->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => $user->id,
                'cancellation_reason' => 'File fermée par un administrateur'
            ]);

        return redirect()->back()
            ->with('success', 'La file a été fermée avec succès. Les tickets en attente ont été annulés.');
    }

    /**
     * Rouvre une file d'attente fermée
     *
     * @param  \App\Models\Queue  $queue
     * @return \Illuminate\Http\Response
     */
    public function reopen(Queue $queue)
    {
        $user = auth()->user();

        // Vérifier les permissions
        if (!$this->userCanManageQueue($user, $queue)) {
            abort(403, 'Vous n\'avez pas la permission de rouvrir cette file d\'attente.');
        }

        // Si la file n'est pas fermée, on ne peut pas la rouvrir
        if ($queue->status !== QueueStatus::CLOSED) {
            return redirect()->back()
                ->with('error', 'Impossible de rouvrir une file qui n\'est pas fermée.');
        }

        // Rouvrir la file avec le statut "open"
        $queue->update(['status' => QueueStatus::OPEN]);

        return redirect()->back()
            ->with('success', 'La file a été rouverte avec succès.');
    }

    /**
     * Annule tous les tickets en attente d'une file d'attente
     *
     * @param  \App\Models\Queue  $queue
     * @return \Illuminate\Http\Response
     */
    public function cancelPendingTickets(Queue $queue)
    {
        $user = auth()->user();

        // Vérifier les permissions
        if (!$this->userCanManageQueue($user, $queue)) {
            abort(403, 'Vous n\'avez pas la permission d\'annuler les tickets de cette file d\'attente.');
        }

        // Compter le nombre de tickets annulés
        $count = $queue->tickets()
            ->whereIn('status', ['waiting', 'called'])
            ->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => $user->id,
                'cancellation_reason' => 'Annulation manuelle par un administrateur'
            ]);

        if ($count > 0) {
            return redirect()->back()
                ->with('success', "{$count} ticket(s) en attente ont été annulés avec succès.");
        }

        return redirect()->back()
            ->with('info', 'Aucun ticket en attente à annuler.');
    }
}
