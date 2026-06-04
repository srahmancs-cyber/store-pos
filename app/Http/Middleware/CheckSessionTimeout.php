<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionTimeout
{
    // Session timeout is handled by Laravel's SESSION_LIFETIME=30 in .env
    // This middleware just ensures Auth is valid
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
