<?php

namespace App\Http\Middleware;

use App\Services\LoginAttemptLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WarnApiLoginCredentialsInQueryString
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/login')
            && ($request->query->has('email') || $request->query->has('password'))) {
            app(LoginAttemptLogger::class)->credentialsInQueryString($request);
        }

        return $next($request);
    }
}
