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
        if (!$request->user()) {
            abort(401, 'Non authentifié.');
        }

        if (!$request->user()->hasPermission($permission)) {
            abort(403, 'Permission insuffisante pour accéder à cette ressource.');
        }

        return $next($request);
    }
}
