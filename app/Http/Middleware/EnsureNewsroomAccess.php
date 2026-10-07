<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNewsroomAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('admin', 'editor', 'reporter')) {
            abort(403);
        }

        $user->loadMissing('roles.permissions');

        return $next($request);
    }
}
