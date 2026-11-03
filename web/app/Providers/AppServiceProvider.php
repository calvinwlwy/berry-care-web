<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Services\LoginAttemptLogger;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->isProduction()) {
            if (config('app.debug')) {
                Log::warning('APP_DEBUG aktif pada environment production; debug dipaksa nonaktif.');
            }

            config(['app.debug' => false]);
            URL::forceScheme('https');
        }

        RateLimiter::for('api-login', function (Request $request) {
            $key = Str::transliterate(
                Str::lower((string) $request->input('email')).'|'.$request->ip()
            );

            $response = function (Request $request, array $headers): \Illuminate\Http\JsonResponse {
                app(LoginAttemptLogger::class)->failed(
                    $request,
                    $request->input('email'),
                    'rate_limited'
                );

                return response()->json([
                    'message' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam satu menit.',
                ], 429, $headers);
            };

            return [
                Limit::perMinute(5)->by($key)->response($response),
                Limit::perMinute(20)->by('api-login-ip|'.$request->ip())->response($response),
            ];
        });

        RateLimiter::for('web-login-ip', fn (Request $request): Limit => Limit::perMinute(20)
            ->by('web-login-ip|'.$request->ip())
            ->response(function (Request $request, array $headers) {
                app(LoginAttemptLogger::class)->failed(
                    $request,
                    $request->input('email'),
                    'rate_limited'
                );

                $response = redirect()->route('login')->withErrors([
                    'email' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam satu menit.',
                ]);
                $response->setStatusCode(429);
                $response->headers->add($headers);

                return $response;
            }));

        RateLimiter::for('api-register', fn (Request $request): Limit => Limit::perMinute(5)
            ->by('register|'.$request->ip())
            ->response(fn () => response()->json([
                'message' => 'Terlalu banyak percobaan registrasi. Silakan coba lagi dalam satu menit.',
            ], 429)));
    }
}
