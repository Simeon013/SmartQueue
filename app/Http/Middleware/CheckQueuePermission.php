<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Queue;

class CheckQueuePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permissionType = null): Response
    {
        if (!$request->user()) {
            abort(401, 'Non authentifié.');
        }

        // Récupérer la file d'attente depuis la route
        $queue = $request->route('queue');

        if (!$queue) {
            abort(404, 'File d\'attente non trouvée.');
        }

        // Les super admins ont accès à tout
        if ($request->user()->isSuperAdmin()) {
            return $next($request);
        }

        // Vérifier si l'utilisateur a une permission sur cette file
        if (!$request->user()->hasQueuePermission($queue, $permissionType)) {
            abort(403, 'Vous n\'avez pas les permissions nécessaires pour accéder à cette file d\'attente.');
        }

        return $next($request);
    }
}
