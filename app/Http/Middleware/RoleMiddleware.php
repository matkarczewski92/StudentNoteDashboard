<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user || (count($roles) && !in_array($user->role, $roles, true))) {
            abort(403, 'Brak uprawnień.');
        }

        return $next($request);
    }
}
