<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $roleSlug): Response
    {
        $user = $request->user();
        
        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            abort(403, 'Vous devez être connecté pour accéder à cette ressource.');
        }

        // Convertir le slug du rôle en enum
        $role = UserRole::tryFrom($roleSlug);
        
        if (!$role) {
            abort(500, 'Rôle non valide.');
        }

        // Vérifier si l'utilisateur a le rôle requis
        if (!$user->hasRole($role)) {
            abort(403, 'Vous n\'avez pas les droits nécessaires pour accéder à cette ressource.');
        }

        return $next($request);
    }
}
