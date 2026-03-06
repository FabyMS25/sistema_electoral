<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{

    public function handle(Request $request, Closure $next, $permission)
    {
        if (!Auth::check()) {
            abort(403, 'No autenticado');
        }
        $user = Auth::user();
        if ($user->hasRole('administrador')) {
            return $next($request);
        }
        if (!$user->hasPermission($permission)) {
            abort(403, 'No tienes permiso para realizar esta acción');
        }
        return $next($request);
    }
}
