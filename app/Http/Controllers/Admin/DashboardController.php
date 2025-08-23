<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Queue;
use App\Models\Ticket;
use App\Models\QueueUser;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Afficher le tableau de bord approprié selon le rôle de l'utilisateur
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            return $this->superAdminDashboard($user);
        }

        if ($user->isAdmin()) {
            return $this->adminDashboard($user);
        }

        return $this->agentDashboard($user);
    }

    /**
     * Tableau de bord Super Admin
     */
    protected function superAdminDashboard(User $user): View
    {
        // 1. Statistiques des files d'attente
        $queueStats = [
            'total_queues' => Queue::count(),
            'active_queues' => Queue::where('status', 'open')->count(),
        ];

        // 2. Répartition des utilisateurs
        $userStats = [
            'super_admins' => User::where('role', UserRole::SUPER_ADMIN->value)->count(),
            'admins' => User::where('role', UserRole::ADMIN->value)->count(),
            'agents' => User::where('role', UserRole::AGENT->value)->count(),
            'total_users' => User::count(),
        ];

        // Vérification des rôles dans la base de données
        // Log::info('Rôles trouvés dans la base de données : ' . json_encode([
        //     'super_admins' => User::where('role', UserRole::SUPER_ADMIN->value)->get(['id', 'name', 'role'])->toArray(),
        //     'admins' => User::where('role', UserRole::ADMIN->value)->get(['id', 'name', 'role'])->toArray(),
        //     'agents' => User::where('role', UserRole::AGENT->value)->get(['id', 'name', 'role'])->toArray(),
        // ]));

        // 3. Avis clients
        $reviewStats = [
            'average_rating' => \App\Models\Review::whereNotNull('submitted_at')
                ->avg('rating') ?? 0,
            'total_reviews' => \App\Models\Review::whereNotNull('submitted_at')->count(),
        ];

        // Formater la note moyenne avec 1 décimale
        $reviewStats['average_rating'] = round($reviewStats['average_rating'], 1);

        // 4. Statistiques de la journée
        $todayStats = [
            'tickets_created' => Ticket::whereDate('created_at', today())->count(),
            'tickets_served' => Ticket::whereDate('served_at', today())->where('status', 'served')->count(),
            'tickets_pending' => Ticket::where('status', 'waiting')->count(),
            'active_agents' => User::whereIn('role', [
                UserRole::SUPER_ADMIN->value,
                UserRole::ADMIN->value,
                UserRole::AGENT->value
            ])->where('is_active', true)->count(),
        ];

        // Récupérer les 5 derniers utilisateurs inscrits
        $recentUsers = User::latest()->take(5)->get(['id', 'name', 'email', 'created_at']);

        // Statistiques d'activité récente
        $recentActivities = collect([]); // Initialisation avec une collection vide

        return view('admin.dashboard.super-admin.index', [
            'user' => $user,
            'title' => 'Tableau de bord Super Admin',
            'queueStats' => $queueStats,
            'userStats' => $userStats,
            'reviewStats' => $reviewStats,
            'todayStats' => $todayStats,
            'recentUsers' => $recentUsers,
            'recentActivities' => $recentActivities
        ]);
    }

    /**
     * Tableau de bord Admin
     */
    protected function adminDashboard(User $user): View
    {
        // Récupérer toutes les files d'attente avec les permissions de l'utilisateur
        $allQueues = Queue::with(['permissions' => function($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->withCount(['tickets as pending_tickets_count' => function($query) {
                $query->where('status', 'waiting');
            }])
            ->withCount('tickets')
            ->latest()
            ->get()
            ->map(function($queue) use ($user) {
                // Déterminer le niveau d'accès
                $queue->permission_type = null;
                $queue->is_owner = false;

                // Vérifier les permissions spécifiques
                $permission = $queue->permissions->first();
                if ($permission) {
                    $queue->permission_type = $permission->permission_type;
                    $queue->is_owner = $permission->permission_type === 'owner';
                }

                // Si c'est le créateur, on force le statut owner
                if ($queue->created_by === $user->id) {
                    $queue->is_owner = true;
                    $queue->permission_type = 'owner';
                }

                return $queue;
            });

        // Récupérer les files créées par l'utilisateur
        $managedQueues = $allQueues->where('is_owner', true);
        $queueIds = $allQueues->pluck('id');

        // Récupérer les tickets récents des files d'attente gérées par l'administrateur
        $recentTickets = collect([]);
        if ($queueIds->isNotEmpty()) {
            $recentTickets = Ticket::with(['queue', 'user'])
                ->whereIn('queue_id', $queueIds)
                ->where('status', 'served')
                ->latest('served_at')
                ->take(5)
                ->get()
                ->map(function($ticket) {
                    $ticket->customer_name = $ticket->user ? $ticket->user->name : 'Client inconnu';
                    return $ticket;
                });
        }

        // Initialiser les statistiques
        $stats = [
            'total_queues' => 0,
            'active_queues' => 0,
            'pending_tickets' => 0,
            'active_agents' => 0,
        ];

        // Mettre à jour les statistiques si l'utilisateur a des files d'attente
        if ($queueIds->isNotEmpty()) {
            $stats = [
                'total_queues' => $managedQueues->count(),
                'active_queues' => $managedQueues->where('status', 'open')->count(),
                'pending_tickets' => Ticket::whereIn('queue_id', $queueIds)
                    ->where('status', 'waiting')
                    ->count(),
                'active_agents' => User::where('role', 'agent')
                    ->whereHas('queues', function($query) use ($queueIds) {
                        $query->whereIn('queues.id', $queueIds);
                    })
                    ->count(),
            ];
        }

        return view('admin.dashboard.admin.index', [
            'user' => $user,
            'title' => 'Tableau de bord Administrateur',
            'allQueues' => $allQueues,
            'managedQueues' => $managedQueues,
            'recentTickets' => $recentTickets,
            'stats' => [
                'total_queues' => $allQueues->count(),
                'active_queues' => $allQueues->where('status', 'open')->count(),
                'pending_tickets' => $allQueues->sum('pending_tickets_count'),
                'active_agents' => User::where('role', 'agent')
                    ->whereHas('queues', function($query) use ($queueIds) {
                        $query->whereIn('queues.id', $queueIds);
                    })
                    ->count(),
            ]
        ]);
    }

    /**
     * Tableau de bord Agent
     */
    protected function agentDashboard(User $user): View
    {
        // Récupérer toutes les files d'attente avec les permissions de l'agent
        $assignedQueues = Queue::with(['permissions' => function($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->whereHas('permissions', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['service'])
            ->get()
            ->map(function($queue) use ($user) {
                // Déterminer le niveau d'accès
                $permission = $queue->permissions->first();
                $queue->permission_type = $permission ? $permission->permission_type : null;
                return $queue;
            });

        // Tickets en cours pour cet agent
        $currentTickets = Ticket::with(['queue', 'queue.service'])
            ->where('handled_by', $user->id)
            ->whereIn('status', ['processing', 'called'])
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        // Prochain ticket dans les files d'attente assignées
        $nextTicket = null;
        if ($assignedQueues->isNotEmpty()) {
            $nextTicket = Ticket::whereIn('queue_id', $assignedQueues->pluck('id'))
                ->where('status', 'waiting')
                ->orderBy('created_at')
                ->first();
        }

        // Statistiques de l'agent
        $stats = [
            'tickets_today' => Ticket::where('handled_by', $user->id)
                ->whereDate('handled_at', today())
                ->count(),
            'tickets_week' => Ticket::where('handled_by', $user->id)
                ->whereBetween('handled_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'average_processing_time' => $this->getAverageProcessingTime($user),
        ];

        return view('admin.dashboard.agent.index', [
            'user' => $user,
            'title' => 'Tableau de bord Agent',
            'assignedQueues' => $assignedQueues,
            'currentTickets' => $currentTickets,
            'nextTicket' => $nextTicket,
            'stats' => $stats
        ]);
    }

    /**
     * Calculer le temps moyen de traitement des tickets par l'agent
     */
    protected function getAverageProcessingTime(User $user): ?int
    {
        $result = $user->tickets()
            ->select(DB::raw('AVG(TIMESTAMPDIFF(SECOND, called_at, handled_at)) as avg_processing_time'))
            ->whereNotNull('called_at')
            ->whereNotNull('handled_at')
            ->first();

        return $result ? (int) $result->avg_processing_time : null;
    }
}
