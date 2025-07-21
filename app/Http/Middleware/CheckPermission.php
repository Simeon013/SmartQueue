<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        
        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            abort(401, 'Vous devez être connecté pour accéder à cette ressource.');
        }

        // Vérifier si l'utilisateur a la permission requise via son rôle
        if (!$user->can($permission)) {
            abort(403, 'Vous n\'avez pas les permissions nécessaires pour accéder à cette ressource.');
        }

        return $next($request);
    }
}
