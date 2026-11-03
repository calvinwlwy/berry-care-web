<?php

namespace Tests\Feature;

use App\Http\Middleware\SecurityHeaders;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SecurityConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_provider_forces_debug_off_and_https_urls(): void
    {
        $previousEnvironment = app()->environment();
        app()->detectEnvironment(fn (): string => 'production');
        config(['app.debug' => true]);
        Log::spy();

        (new AppServiceProvider(app()))->boot();

        $this->assertFalse(config('app.debug'));
        $this->assertStringStartsWith('https://', URL::to('/'));
        Log::shouldHaveReceived('warning')->with(
            'APP_DEBUG aktif pada environment production; debug dipaksa nonaktif.'
        );

        URL::forceScheme(null);
        app()->detectEnvironment(fn (): string => $previousEnvironment);
    }

    public function test_hsts_is_only_added_for_secure_production_requests(): void
    {
        $middleware = new SecurityHeaders();
        $previousEnvironment = app()->environment();
        app()->detectEnvironment(fn (): string => 'production');

        $secureResponse = $middleware->handle(
            Request::create('https://berrycare.example.test/', 'GET'),
            fn () => response('ok')
        );
        $insecureResponse = $middleware->handle(
            Request::create('http://berrycare.example.test/', 'GET'),
            fn () => response('ok')
        );

        $this->assertSame(
            'max-age=31536000; includeSubDomains',
            $secureResponse->headers->get('Strict-Transport-Security')
        );
        $this->assertNull($insecureResponse->headers->get('Strict-Transport-Security'));

        app()->detectEnvironment(fn (): string => $previousEnvironment);
    }

    public function test_local_flutter_web_origin_passes_api_cors_preflight(): void
    {
        $this->call('OPTIONS', '/api/register', [], [], [], [
            'HTTP_ORIGIN' => 'http://localhost:51327',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
            'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'content-type',
        ])->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:51327')
            ->assertHeader('Access-Control-Allow-Methods')
            ->assertHeader('Access-Control-Allow-Headers');
    }

    public function test_production_cors_allows_configured_origin_only(): void
    {
        $previousEnvironment = app()->environment();
        app()->detectEnvironment(fn (): string => 'production');
        config([
            'cors.allowed_origins' => ['https://app.berrycare.example'],
            'cors.allowed_origins_patterns' => [],
        ]);

        $this->call('OPTIONS', '/api/register', [], [], [], [
            'HTTP_ORIGIN' => 'https://app.berrycare.example',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ])->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', 'https://app.berrycare.example');

        $cors = new \Fruitcake\Cors\CorsService(config('cors'));
        $this->assertFalse($cors->isOriginAllowed(
            Request::create('https://untrusted.example', 'OPTIONS', [], [], [], [
                'HTTP_ORIGIN' => 'https://untrusted.example',
            ])
        ));

        app()->detectEnvironment(fn (): string => $previousEnvironment);
    }
}
