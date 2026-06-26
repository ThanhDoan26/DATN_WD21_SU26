<?php

namespace App\Http\Middleware;

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
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user() || !$request->user()->role) {
            abort(403, 'Unauthorized access.');
        }

        if (!in_array($request->user()->role->role_name, $roles)) {
            abort(403, 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}
