<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EnsureRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        if (! $user) {
            throw new AccessDeniedHttpException('Authentication required.');
        }

        if (empty($roles) || $user->hasRole(...$roles)) {
            return $next($request);
        }

        throw new AccessDeniedHttpException('Insufficient permissions.');
    }
}
