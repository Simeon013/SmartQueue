<?php

namespace App\Http\Middleware;

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
        if (!auth()->check()) {
            abort(403, 'Accès non autorisé.');
        }

        $user = auth()->user();
        
        // Vérifier si l'utilisateur a au moins une permission d'administration
        $hasAdminPermission = $user->hasAnyPermission([
            'users.manage',
            'roles.manage', 
            'settings.manage',
            'queues.view',
            'queues.create',
            'queues.edit',
            'queues.delete'
        ]);

        if (!$hasAdminPermission && !$user->hasRole('super-admin')) {
            abort(403, 'Accès non autorisé.');
        }

        return $next($request);
    }
}
