<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\Queue;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckQueuePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permissionType = null): Response
    {
        $user = $request->user();
        
        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            abort(401, 'Vous devez être connecté pour accéder à cette ressource.');
        }

        // Récupérer la file d'attente depuis la route
        $queue = $request->route('queue');

        if (!$queue) {
            abort(404, 'File d\'attente non trouvée.');
        }

        // Les super admins ont accès à tout
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Vérifier les permissions en fonction du rôle
        if ($user->isAdmin()) {
            // Les admins ont accès à toutes les files
            return $next($request);
        }

        // Pour les agents, vérifier les permissions sur la file
        if ($user->isAgent()) {
            // Vérifier d'abord si l'utilisateur a la permission de gérer cette file
            if ($permissionType === 'manage' && $queue->userCanManage($user)) {
                return $next($request);
            }
            
            // Vérifier si l'utilisateur a la permission d'opérer sur cette file
            if ($permissionType === 'operate' && $queue->userCanOperate($user)) {
                return $next($request);
            }
            
            // Vérifier si cette file est assignée à l'agent (pour rétrocompatibilité)
            if (isset($queue->assigned_to) && $queue->assigned_to === $user->id) {
                return $next($request);
            }
            
            // Vérifier les permissions spécifiques si nécessaire
            if ($permissionType && !$user->can($permissionType)) {
                abort(403, 'Vous n\'avez pas les permissions nécessaires pour effectuer cette action sur cette file.');
            }
        }

        // Si on arrive ici, l'utilisateur n'a pas les droits nécessaires
        abort(403, 'Vous n\'avez pas les permissions nécessaires pour accéder à cette file d\'attente.');
    }
}
