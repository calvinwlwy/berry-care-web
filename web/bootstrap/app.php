<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\WarnApiLoginCredentialsInQueryString;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $trustedProxies = trim((string) env('TRUSTED_PROXIES', ''));
        $middleware->trustProxies(
            at: $trustedProxies === ''
                ? null
                : array_map('trim', explode(',', $trustedProxies))
        );
        $middleware->append(WarnApiLoginCredentialsInQueryString::class);
        $middleware->append(SecurityHeaders::class);
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request): bool => $request->is('api/*')
        );
        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Tidak terautentikasi.'], 401);
            }
        });
        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if ($request->is('api/*')) {
                return match ($exception->getStatusCode()) {
                    403 => response()->json(['message' => 'Akses ditolak.'], 403),
                    404 => response()->json(['message' => 'Data tidak ditemukan.'], 404),
                    default => null,
                };
            }
        });
    })->create();
