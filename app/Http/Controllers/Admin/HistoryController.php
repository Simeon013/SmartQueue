<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\UserRole;
use App\Models\Queue;
use App\Models\Ticket;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est admin ou super-admin
        if (!$user->hasRole([UserRole::ADMIN, UserRole::SUPER_ADMIN])) {
            abort(403, 'Accès non autorisé. Vous devez être administrateur pour accéder à cette section.');
        }

        // Récupérer les paramètres de filtrage
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $search = $request->input('search');
        $status = $request->input('status');
        $serviceId = $request->input('service_id');
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        // Initialiser la requête avec les relations
        $query = Queue::with([
            'establishment:id,name',
            'service:id,name,color',
            'tickets' => function($q) {
                $q->select('id', 'queue_id', 'status', 'created_at', 'called_at', 'served_at')
                  ->withTrashed();
            }
        ]);

        // Appliquer les filtres
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('establishment', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filtrer par statut en utilisant les valeurs de l'énumération
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // Filtrer par service
        if ($serviceId) {
            $query->where('service_id', $serviceId);
        }

        if ($startDate) {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $endDate = Carbon::parse($endDate)->endOfDay();
            $query->where('created_at', '<=', $endDate);
        }

        // Appliquer le tri
        $validSorts = ['name', 'status', 'created_at', 'end_time'];
        $sort = in_array($sort, $validSorts) ? $sort : 'created_at';
        $direction = in_array(strtolower($direction), ['asc', 'desc']) ? $direction : 'desc';

        $query->orderBy($sort, $direction);

        // Paginer les résultats avec le bon nombre d'éléments par page
        $perPage = $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;
        $queues = $query->paginate($perPage)->withQueryString();

        // Calculer les statistiques
        $stats = $this->calculateStatistics($startDate, $endDate);

        // Récupérer la liste des services pour le filtre
        $services = Service::orderBy('name')->get(['id', 'name']);

        return view('admin.history.index', compact(
            'queues',
            'stats',
            'services',
            'search',
            'status',
            'serviceId',
            'sort',
            'direction',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Calcule les statistiques globales
     */
    protected function calculateStatistics($dateFrom = null, $dateTo = null)
    {
        $baseQuery = Queue::query();
        
        // Créer une requête de base pour les tickets avec les filtres de date
        $ticketBaseQuery = function($query) use ($dateFrom, $dateTo) {
            if ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->where('created_at', '<=', $dateTo . ' 23:59:59');
            }
        };

        // Compter les tickets avec une seule requête groupée pour plus d'efficacité
        $ticketCounts = Ticket::when($dateFrom || $dateTo, $ticketBaseQuery)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = "waiting" THEN 1 ELSE 0 END) as waiting')
            ->selectRaw('SUM(CASE WHEN status = "paused" THEN 1 ELSE 0 END) as paused')
            ->selectRaw('SUM(CASE WHEN status = "served" THEN 1 ELSE 0 END) as served')
            ->selectRaw('SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled')
            ->selectRaw('SUM(CASE WHEN status IN ("served", "cancelled", "skipped", "noshow") THEN 1 ELSE 0 END) as completed')
            ->first();

        // Statistiques de base
        $stats = [
            'total_queues' => $baseQuery->count(),
            'active_queues' => (clone $baseQuery)->where('status', 'open')->count(),
            'closed_queues' => (clone $baseQuery)->where('status', 'closed')->count(),
            'total_tickets' => $ticketCounts->total ?? 0,
            'waiting_tickets' => $ticketCounts->waiting ?? 0,
            'paused_tickets' => $ticketCounts->paused ?? 0,
            'served_tickets' => $ticketCounts->served ?? 0,
            'cancelled_tickets' => $ticketCounts->cancelled ?? 0,
            'completed_tickets' => $ticketCounts->completed ?? 0,
        ];

        // Pourcentage de tickets terminés
        $stats['completed_percentage'] = $stats['total_tickets'] > 0
            ? round(($stats['completed_tickets'] / $stats['total_tickets']) * 100)
            : 0;

        // Calculer les temps moyens avec une seule requête
        $avgTimes = Ticket::whereIn('status', ['served', 'cancelled', 'skipped', 'noshow'])
            ->when($dateFrom, function($q) use ($dateFrom) {
                $q->where('created_at', '>=', $dateFrom);
            })
            ->when($dateTo, function($q) use ($dateTo) {
                $q->where('created_at', '<=', $dateTo . ' 23:59:59');
            })
            ->select([
                DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, served_at)) as avg_wait'),
                DB::raw('AVG(TIMESTAMPDIFF(MINUTE, called_at, served_at)) as avg_service')
            ])
            ->whereNotNull('called_at')
            ->first();

        $stats['avg_wait_time'] = round($avgTimes->avg_wait ?? 0, 1);
        $stats['avg_service_time'] = round($avgTimes->avg_service ?? 0, 1);

        // Statistiques quotidiennes
        $today = Carbon::today();
        $stats['today_tickets'] = Ticket::whereDate('created_at', $today)->count();
        $stats['today_queues'] = Queue::whereDate('created_at', $today)->count();

        return $stats;
    }

    /**
     * Get statistics for AJAX requests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats(Request $request)
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est admin ou super-admin
        if (!$user->hasRole([UserRole::ADMIN, UserRole::SUPER_ADMIN])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Récupérer les paramètres de filtrage avec des valeurs par défaut
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Si aucune date n'est spécifiée, utiliser les 30 derniers jours
        if (!$startDate && !$endDate) {
            $startDate = Carbon::now()->subDays(30)->format('Y-m-d');
            $endDate = Carbon::now()->format('Y-m-d');
        }

        // Calculer les statistiques avec les dates fournies
        $stats = $this->calculateStatistics($startDate, $endDate);

        return response()->json($stats);
    }
}
