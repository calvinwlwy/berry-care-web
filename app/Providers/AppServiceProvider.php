<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

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
        RateLimiter::for('api-login', function (Request $request): Limit {
            $key = Str::transliterate(
                Str::lower((string) $request->input('email')).'|'.$request->ip()
            );

            return Limit::perMinute(5)
                ->by($key)
                ->response(fn () => response()->json([
                    'message' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam satu menit.',
                ], 429));
        });

        RateLimiter::for('api-register', fn (Request $request): Limit => Limit::perMinute(5)
            ->by('register|'.$request->ip())
            ->response(fn () => response()->json([
                'message' => 'Terlalu banyak percobaan registrasi. Silakan coba lagi dalam satu menit.',
            ], 429)));
    }
}
