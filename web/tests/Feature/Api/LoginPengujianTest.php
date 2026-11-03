<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\TestCase;

class LoginPengujianTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_login_sukses_returns_user_and_token_without_password(): void
    {
        $password = 'Password123';
        $user = User::factory()->create(['password' => $password]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Login berhasil.')
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.name', $user->name)
            ->assertJsonPath('user.email', $user->email)
            ->assertJsonPath('user.role', $user->role)
            ->assertJsonStructure(['message', 'user' => ['id', 'name', 'email', 'role'], 'token'])
            ->assertJsonMissingPath('user.password')
            ->assertJsonMissingPath('password')
            ->assertDontSee($password);

        $this->assertTrue(Hash::check($password, $user->fresh()->password));
    }

    public function test_b_wrong_password_returns_generic_unauthorized_response(): void
    {
        User::factory()->create(['email' => 'known@example.test']);

        $response = $this->postJson('/api/login', [
            'email' => 'known@example.test',
            'password' => 'WrongPassword123',
        ]);

        $response->assertUnauthorized()
            ->assertExactJson(['message' => 'Email atau password salah'])
            ->assertJsonMissingPath('token');
    }

    public function test_c_unknown_email_returns_exactly_the_same_login_failure(): void
    {
        User::factory()->create(['email' => 'known@example.test']);

        $knownResponse = $this->postJson('/api/login', [
            'email' => 'known@example.test',
            'password' => 'WrongPassword123',
        ]);
        $unknownResponse = $this->postJson('/api/login', [
            'email' => 'missing@example.test',
            'password' => 'WrongPassword123',
        ]);

        $knownResponse->assertUnauthorized()
            ->assertExactJson(['message' => 'Email atau password salah']);
        $unknownResponse->assertUnauthorized()
            ->assertExactJson(['message' => 'Email atau password salah']);
        $this->assertSame($knownResponse->getContent(), $unknownResponse->getContent());
    }

    public function test_d1_empty_email_returns_field_validation_error(): void
    {
        $this->postJson('/api/login', ['email' => '', 'password' => 'Password123'])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Data yang diberikan tidak valid.')
            ->assertJsonPath('errors.email.0', 'Email wajib diisi.')
            ->assertJsonMissingPath('errors.password');
    }

    public function test_d2_empty_password_returns_field_validation_error(): void
    {
        $this->postJson('/api/login', [
            'email' => 'valid@example.test',
            'password' => '',
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Data yang diberikan tidak valid.')
            ->assertJsonPath('errors.password.0', 'Password wajib diisi.')
            ->assertJsonMissingPath('errors.email');
    }

    public function test_d3_empty_body_returns_errors_for_both_fields(): void
    {
        $this->postJson('/api/login', [])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Data yang diberikan tidak valid.')
            ->assertJsonPath('errors.email.0', 'Email wajib diisi.')
            ->assertJsonPath('errors.password.0', 'Password wajib diisi.');
    }

    public function test_d4_invalid_email_format_returns_validation_error(): void
    {
        $this->postJson('/api/login', [
            'email' => 'bukan-email',
            'password' => 'Password123',
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Data yang diberikan tidak valid.')
            ->assertJsonPath('errors.email.0', 'Format email tidak valid.');
    }

    public function test_e_user_endpoint_checks_missing_valid_and_fake_tokens(): void
    {
        $this->getJson('/api/user')->assertUnauthorized()
            ->assertExactJson(['message' => 'Tidak terautentikasi.']);

        $user = User::factory()->create();
        $token = $user->createToken('login-test')->plainTextToken;
        $this->withToken($token)->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonMissingPath('user.password');

        Auth::forgetGuards();
        $this->withToken('not-a-real-token')->getJson('/api/user')
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Tidak terautentikasi.']);
    }

    public function test_f_api_unauthenticated_response_is_json_for_wildcard_accept_header(): void
    {
        $response = $this->withHeaders(['Accept' => '*/*'])->get('/api/user');

        $response->assertUnauthorized()
            ->assertHeader('content-type', 'application/json')
            ->assertExactJson(['message' => 'Tidak terautentikasi.']);
    }

    public function test_g_login_rate_limit_returns_retry_after_header_and_json_message(): void
    {
        $email = 'ratelimit-audit@example.test';
        $key = Str::transliterate(Str::lower($email).'|127.0.0.1');
        RateLimiter::clear($key);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/login', [
                'email' => $email,
                'password' => 'WrongPassword123',
            ])->assertUnauthorized();
        }

        $response = $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'WrongPassword123',
        ]);

        $response->assertStatus(429)
            ->assertJsonPath(
                'message',
                'Terlalu banyak percobaan login. Silakan coba lagi dalam satu menit.'
            )
            ->assertHeader('Retry-After');
        $this->assertGreaterThan(0, (int) $response->headers->get('Retry-After'));
        $this->assertLessThanOrEqual(60, (int) $response->headers->get('Retry-After'));

        RateLimiter::clear($key);
    }

    public function test_g_login_rate_limit_also_enforces_twenty_attempts_per_ip(): void
    {
        $ipKey = 'api-login-ip|127.0.0.1';
        RateLimiter::clear($ipKey);

        for ($attempt = 0; $attempt < 20; $attempt++) {
            $this->postJson('/api/login', [
                'email' => "different-{$attempt}@example.test",
                'password' => 'WrongPassword123',
            ])->assertUnauthorized();
        }

        $this->postJson('/api/login', [
            'email' => 'different-21@example.test',
            'password' => 'WrongPassword123',
        ])->assertStatus(429)
            ->assertJsonPath(
                'message',
                'Terlalu banyak percobaan login. Silakan coba lagi dalam satu menit.'
            )
            ->assertHeader('Retry-After');

        RateLimiter::clear($ipKey);
    }

    public function test_h_logout_revokes_the_token_used_to_log_out(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('login-test')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')
            ->assertOk()
            ->assertExactJson(['message' => 'Logout berhasil.']);

        Auth::forgetGuards();
        $this->withToken($token)->getJson('/api/user')
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Tidak terautentikasi.']);
    }

    public function test_i_malicious_email_is_safe_and_very_long_password_returns_422(): void
    {
        $user = User::factory()->create(['password' => 'CorrectPassword123']);

        $sqlEmailResponse = $this->postJson('/api/login', [
            'email' => "' OR '1'='1",
            'password' => 'Password123',
        ]);
        $this->assertContains($sqlEmailResponse->getStatusCode(), [401, 422]);
        $this->assertNotSame(500, $sqlEmailResponse->getStatusCode());

        $longPasswordResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => str_repeat('x', 10000),
        ]);
        $longPasswordResponse->assertUnprocessable()
            ->assertJsonPath('errors.password.0', 'Password maksimal 255 karakter.');
    }

    public function test_j_login_and_profile_responses_never_expose_password_or_remember_token(): void
    {
        $password = 'Password123';
        $user = User::factory()->create(['password' => $password]);
        $login = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);
        $login->assertOk()
            ->assertJsonMissingPath('user.password')
            ->assertJsonMissingPath('user.remember_token')
            ->assertDontSee($user->fresh()->password)
            ->assertDontSee($user->fresh()->getRememberToken());

        $token = $login->json('token');
        $this->withToken($token)->getJson('/api/user')
            ->assertOk()
            ->assertJsonMissingPath('user.password')
            ->assertJsonMissingPath('user.remember_token')
            ->assertDontSee($user->fresh()->password)
            ->assertDontSee($user->fresh()->getRememberToken());

        $this->assertTrue(Hash::check($password, $user->fresh()->password));
    }

    public function test_token_past_configured_expiration_is_rejected(): void
    {
        config(['sanctum.expiration' => 10080]);
        $user = User::factory()->create();
        $token = $user->createToken('expiring-token')->plainTextToken;

        $this->travel(10081)->minutes();

        $this->withToken($token)->getJson('/api/user')
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Tidak terautentikasi.']);
    }

    public function test_login_and_profile_responses_have_security_and_no_store_headers(): void
    {
        $user = User::factory()->create(['password' => 'Password123']);
        $login = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'Password123',
        ]);
        $login->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()')
            ->assertHeader('Cache-Control', 'no-store, private');

        $profile = $this->withToken($login->json('token'))->getJson('/api/user');
        $profile->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('no-store', $profile->headers->get('Cache-Control'));
    }

    public function test_login_credentials_in_query_string_are_logged_safely(): void
    {
        Log::spy();

        $this->call(
            'POST',
            '/api/login?email=private.user%40example.test&password=never-log-this'
        )->assertUnauthorized();

        Log::shouldHaveReceived('warning')->withArgs(function (string $message, array $context): bool {
            return $message === 'Login attempt failed'
                && ($context['email'] ?? null) === 'pr***@example.test'
                && ($context['reason'] ?? null) === 'credentials_in_query_string'
                && ! array_key_exists('password', $context)
                && ! str_contains(json_encode($context), 'never-log-this');
        });
    }

    public function test_failed_login_log_masks_email_and_never_contains_password(): void
    {
        Log::spy();

        $this->postJson('/api/login', [
            'email' => 'private.user@example.test',
            'password' => 'NeverLogThisPassword123',
        ])->assertUnauthorized();

        Log::shouldHaveReceived('warning')->withArgs(function (string $message, array $context): bool {
            return $message === 'Login attempt failed'
                && ($context['email'] ?? null) === 'pr***@example.test'
                && ($context['reason'] ?? null) === 'invalid_credentials'
                && ! array_key_exists('password', $context)
                && ! str_contains(json_encode($context), 'NeverLogThisPassword123');
        });
    }
}
