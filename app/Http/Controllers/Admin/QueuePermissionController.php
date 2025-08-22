<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Queue;
use Illuminate\Http\Request;
use App\Models\QueuePermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class QueuePermissionController extends Controller
{
    /**
     * Afficher les permissions d'une file d'attente.
     */
    public function index(Queue $queue)
    {
        $queue->load(['permissions.user', 'permissions.grantedBy']);

        // Récupérer tous les agents
        $agents = User::where('role', 'agent')
            ->orderBy('name')
            ->get();

        // Récupérer les permissions actuelles
        $queuePermissions = $queue->permissions;

        // Déterminer le mode de gestion actuel
        $currentMode = $this->getCurrentPermissionMode($queue);

        return view('admin.queues.permissions', compact('queue', 'agents', 'queuePermissions', 'currentMode'));
    }

    /**
     * Définir le mode de gestion des permissions (tous les agents ou sélectionnés)
     */
    public function setMode(Request $request, Queue $queue)
    {
        $validated = $request->validate([
            'mode' => 'required|in:all_agents_manager,all_agents_operator,selected_agents',
        ]);

        if ($validated['mode'] === 'all_agents_manager') {
            // Supprimer seulement les permissions individuelles non-essentielles (pas owner)
            $queue->permissions()
                ->whereNotNull('user_id')
                ->whereNotIn('permission_type', ['owner'])
                ->delete();

            // Supprimer les permissions globales existantes
            $queue->permissions()->whereNull('user_id')->delete();

            // Ajouter une permission spéciale pour tous les agents (gestion complète)
            $authUser = Auth::user();
            $queue->permissions()->create([
                'user_id' => null, // null = tous les agents
                'permission_type' => 'manager',
                'granted_by' => $authUser ? $authUser->getAuthIdentifier() : null,
            ]);

            return redirect()->back()->with('success', 'Tous les agents peuvent maintenant gérer complètement cette file d\'attente.');
        } elseif ($validated['mode'] === 'all_agents_operator') {
            // Supprimer seulement les permissions individuelles non-essentielles (pas owner/manager)
            $queue->permissions()
                ->whereNotNull('user_id')
                ->whereNotIn('permission_type', ['owner', 'manager'])
                ->delete();

            // Supprimer les permissions globales existantes
            $queue->permissions()->whereNull('user_id')->delete();

            // Ajouter une permission spéciale pour tous les agents (gestion des tickets seulement)
            $authUser = Auth::user();
            $queue->permissions()->create([
                'user_id' => null, // null = tous les agents
                'permission_type' => 'operator',
                'granted_by' => $authUser ? $authUser->getAuthIdentifier() : null,
            ]);

            return redirect()->back()->with('success', 'Tous les agents peuvent maintenant gérer les tickets de cette file d\'attente.');
        } else {
            // Supprimer la permission globale
            $queue->permissions()->whereNull('user_id')->delete();

            return redirect()->back()->with('success', 'Mode de gestion individuelle activé. Vous pouvez maintenant sélectionner des agents spécifiques.');
        }
    }

    /**
     * Ajouter des agents sélectionnés
     */
    public function addSelectedAgents(Request $request, Queue $queue)
    {
        $validated = $request->validate([
            'agent_ids' => 'required|array',
            'agent_ids.*' => 'exists:users,id',
            'permission_type' => 'required|in:manager,operator',
        ]);

        // Supprimer la permission globale si elle existe
        $queue->permissions()->whereNull('user_id')->delete();

        $addedCount = 0;
        foreach ($validated['agent_ids'] as $agentId) {
            // Vérifier si la permission existe déjà
            $existingPermission = $queue->permissions()->where('user_id', $agentId)->first();

            if (!$existingPermission) {
                $authUser = Auth::user();
                $queue->permissions()->create([
                    'user_id' => $agentId,
                    'permission_type' => $validated['permission_type'],
                    'granted_by' => $authUser ? $authUser->getAuthIdentifier() : null,
                ]);
                $addedCount++;
            }
        }

        $message = $addedCount > 0
            ? "{$addedCount} agent(s) ajouté(s) avec succès."
            : "Aucun nouvel agent ajouté (certains avaient déjà des permissions).";

        return redirect()->back()->with('success', $message);
    }

    /**
     * Mettre à jour une permission sur une file d'attente.
     */
    public function updatePermission(Request $request, Queue $queue, QueuePermission $permission)
    {
        $validated = $request->validate([
            'permission_type' => 'required|in:manager,operator',
        ]);

        // Empêcher de modifier le propriétaire
        if ($permission->permission_type === 'owner') {
            return redirect()->back()->with('error', 'Vous ne pouvez pas modifier le propriétaire de la file.');
        }

        $oldType = $permission->permission_type;
        $authUser = auth()->user();
        $permission->update([
            'permission_type' => $validated['permission_type'],
            'granted_by' => $authUser ? $authUser->id : null,
        ]);

        $typeNames = [
            'manager' => 'Gestionnaire',
            'operator' => 'Opérateur'
        ];

        return redirect()->back()->with('success', "Permission de {$permission->user->name} changée de {$typeNames[$oldType]} vers {$typeNames[$validated['permission_type']]}.");
    }

    /**
     * Supprimer une permission sur une file d'attente.
     */
    public function destroy(Queue $queue, QueuePermission $permission)
    {

        // Empêcher de supprimer le propriétaire
        if ($permission->permission_type === 'owner') {
            return redirect()->back()->with('error', 'Vous ne pouvez pas supprimer le propriétaire de la file.');
        }

        $permission->delete();

        return redirect()->back()->with('success', 'Permission supprimée avec succès.');
    }

    /**
     * Afficher les files d'attente d'un utilisateur.
     */
    public function userQueues(User $user)
    {
        $user->load(['queuePermissions.queue', 'queuePermissions.grantedBy']);
        return view('admin.users.queues', compact('user'));
    }

    /**
     * Afficher les utilisateurs d'une file d'attente.
     */
    public function queueUsers(Queue $queue)
    {
        $queue->load(['permissions.user', 'permissions.grantedBy']);
        return view('admin.queues.users', compact('queue'));
    }

    /**
     * Rechercher des utilisateurs pour attribution de permissions.
     */
    public function searchUsers(Request $request, Queue $queue)
    {
        $query = $request->get('q');

        $users = User::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%");
        })
        ->where(function ($q) {
            $q->where('role', 'agent')
              ->orWhereHas('roles', function ($query) {
                  $query->whereIn('slug', ['agent', 'agent-manager']);
              });
        })
        ->whereDoesntHave('queuePermissions', function ($q) use ($queue) {
            $q->where('queue_id', $queue->id);
        })
        ->limit(10)
        ->get(['id', 'name', 'email']);

        return response()->json($users);
    }

    /**
     * Stocker une nouvelle permission pour un utilisateur sur une file d'attente.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'queue_id' => 'required|exists:queues,id',
            'permission_type' => 'required|in:manager,operator',
        ]);

        try {
            // Vérifier si la permission existe déjà
            $existingPermission = QueuePermission::where('user_id', $validated['user_id'])
                ->where('queue_id', $validated['queue_id'])
                ->first();

            if ($existingPermission) {
                return redirect()->back()
                    ->with('error', 'Cet utilisateur a déjà une permission sur cette file d\'attente.');
            }

            // Récupérer l'utilisateur authentifié
            $authUser = Auth::user();

            // Créer la nouvelle permission
            $permission = QueuePermission::create([
                'user_id' => $validated['user_id'],
                'queue_id' => $validated['queue_id'],
                'permission_type' => $validated['permission_type'],
                'granted_by' => $authUser ? $authUser->getAuthIdentifier() : null,
            ]);

            // Journalisation de l'action
            if ($authUser) {
                activity()
                    ->causedBy($authUser)
                    ->performedOn($permission)
                ->withProperties([
                    'user_id' => $validated['user_id'],
                    'queue_id' => $validated['queue_id'],
                    'permission_type' => $validated['permission_type']
                ])
                ->log('Permission attribuée sur une file d\'attente');

                return redirect()->back()
                    ->with('success', 'La permission a été attribuée avec succès.');
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'attribution de la permission : ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de l\'attribution de la permission.');
        }
    }

    /**
     * Déterminer le mode de gestion actuel des permissions
     */
    private function getCurrentPermissionMode(Queue $queue)
    {
        $globalPermission = $queue->permissions()->whereNull('user_id')->first();

        if ($globalPermission) {
            if ($globalPermission->permission_type === 'manager') {
                return 'all_agents_manager';
            } elseif ($globalPermission->permission_type === 'operator') {
                return 'all_agents_operator';
            }
        }

        return 'selected_agents';
    }
}
