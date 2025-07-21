<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // Vérifier si l'utilisateur est connecté et a un rôle d'administrateur
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Accès non autorisé. Vous devez être administrateur pour accéder à cette section.');
        }

        return $next($request);
    }
}
