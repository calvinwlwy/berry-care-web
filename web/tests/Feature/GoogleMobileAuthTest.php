<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\GoogleIdTokenVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class GoogleMobileAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.google.client_id' => 'web-client-id',
            'services.google.auto_register' => true,
        ]);
    }

    public function test_google_login_registers_petani_user_and_returns_safe_response(): void
    {
        Log::spy();
        $this->mockVerifier($this->claims());

        $response = $this->postJson('/api/auth/google', [
            'id_token' => 'test-id-token',
            'role' => 'admin',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Login berhasil.')
            ->assertJsonPath('user.name', 'Petani Google')
            ->assertJsonPath('user.email', 'petani.google@example.test')
            ->assertJsonPath('user.role', 'petani')
            ->assertJsonStructure(['user' => ['id', 'name', 'email', 'role'], 'token'])
            ->assertJsonMissingPath('user.password')
            ->assertJsonMissingPath('user.google_id')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));

        $user = User::where('email', 'petani.google@example.test')->firstOrFail();
        $this->assertSame('google-subject-1', $user->google_id);
        $this->assertSame('petani', $user->role);
        $this->assertNotNull(password_get_info($user->password)['algo']);
        $this->assertNotNull($user->email_verified_at);
        Log::shouldHaveReceived('log')
            ->once()
            ->with('info', 'Google API login attempt', \Mockery::on(
                fn (array $context): bool => $context['email'] === 'pe***@example.test'
                    && ! array_key_exists('id_token', $context)
            ));
    }

    public function test_second_google_login_uses_google_id_without_creating_another_user(): void
    {
        $this->mockVerifier($this->claims());

        $first = $this->postJson('/api/auth/google', ['id_token' => 'test-id-token']);
        $first->assertOk();
        $this->mockVerifier($this->claims(['email' => 'new-address@example.test']));

        $second = $this->postJson('/api/auth/google', ['id_token' => 'test-id-token']);

        $second->assertOk()
            ->assertJsonPath('user.id', $first->json('user.id'));
        $this->assertDatabaseCount('users', 1);
    }

    public function test_invalid_google_token_is_rejected(): void
    {
        $this->mockVerifier(null);

        $this->postJson('/api/auth/google', ['id_token' => 'test-id-token'])
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Token Google tidak valid.']);
    }

    public function test_wrong_audience_is_rejected(): void
    {
        Log::spy();
        $this->mockVerifier($this->claims(['aud' => 'different-client-id']));

        $this->postJson('/api/auth/google', ['id_token' => 'test-id-token'])
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Token Google tidak valid.']);

        Log::shouldHaveReceived('log')
            ->once()
            ->with('warning', 'Google API login attempt', \Mockery::on(
                fn (array $context): bool => $context['reason'] === 'google_client_id_mismatch'
                    && $context['email'] === 'pe***@example.test'
                    && ! array_key_exists('id_token', $context)
            ));
    }

    public function test_unverified_google_email_is_rejected(): void
    {
        $this->mockVerifier($this->claims(['email_verified' => false]));

        $this->postJson('/api/auth/google', ['id_token' => 'test-id-token'])
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Token Google tidak valid.']);
    }

    public function test_expired_token_and_untrusted_issuer_are_rejected(): void
    {
        $this->mockVerifier($this->claims(['exp' => time() - 1]));

        $this->postJson('/api/auth/google', ['id_token' => 'test-id-token'])
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Token Google tidak valid.']);

        $this->mockVerifier($this->claims(['iss' => 'https://attacker.example']));

        $this->postJson('/api/auth/google', ['id_token' => 'test-id-token'])
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Token Google tidak valid.']);
    }

    public function test_unregistered_email_is_rejected_when_auto_registration_is_disabled(): void
    {
        config(['services.google.auto_register' => false]);
        $this->mockVerifier($this->claims());

        $this->postJson('/api/auth/google', ['id_token' => 'test-id-token'])
            ->assertForbidden()
            ->assertExactJson(['message' => 'Akun belum terdaftar. Hubungi admin.']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_existing_email_without_google_id_is_not_linked_automatically(): void
    {
        $existing = User::factory()->create([
            'email' => 'Petani.Google@example.test',
            'name' => 'Nama Lokal',
            'role' => 'admin',
        ]);
        $passwordHash = $existing->password;
        $this->mockVerifier($this->claims());

        $this->postJson('/api/auth/google', ['id_token' => 'test-id-token'])
            ->assertStatus(409)
            ->assertExactJson([
                'message' => 'Email ini sudah terdaftar. Masuk dengan password.',
            ]);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'id' => $existing->id,
            'email' => 'Petani.Google@example.test',
            'name' => 'Nama Lokal',
            'role' => 'admin',
            'google_id' => null,
            'password' => $passwordHash,
        ]);
    }

    public function test_email_linked_to_a_different_google_id_is_rejected(): void
    {
        $existing = User::factory()->create([
            'email' => 'petani.google@example.test',
            'google_id' => 'another-google-subject',
        ]);
        $this->mockVerifier($this->claims());

        $this->postJson('/api/auth/google', ['id_token' => 'test-id-token'])
            ->assertStatus(409)
            ->assertExactJson([
                'message' => 'Email ini sudah terdaftar. Masuk dengan password.',
            ]);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'id' => $existing->id,
            'google_id' => 'another-google-subject',
        ]);
    }

    public function test_sanctum_token_from_google_login_can_be_used_and_revoked(): void
    {
        $this->mockVerifier($this->claims());
        $login = $this->postJson('/api/auth/google', ['id_token' => 'test-id-token'])
            ->assertOk();
        $token = $login->json('token');

        $this->withToken($token)->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('user.id', $login->json('user.id'));
        $this->withToken($token)->postJson('/api/logout')
            ->assertOk();

        Auth::forgetGuards();
        $this->withToken($token)->getJson('/api/user')->assertUnauthorized();
    }

    public function test_google_login_is_rate_limited_per_ip(): void
    {
        $this->mockVerifier($this->claims());

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->postJson('/api/auth/google', [
                'id_token' => 'test-id-token',
            ])->assertOk();
        }

        $this->postJson('/api/auth/google', ['id_token' => 'test-id-token'])
            ->assertStatus(429);
    }

    public function test_empty_and_overlong_google_tokens_return_validation_errors(): void
    {
        $this->postJson('/api/auth/google', ['id_token' => ''])
            ->assertUnprocessable()
            ->assertJsonPath('errors.id_token.0', 'ID token Google wajib diisi.');

        $this->postJson('/api/auth/google', [
            'id_token' => str_repeat('x', 8193),
        ])->assertUnprocessable()
            ->assertJsonPath('errors.id_token.0', 'ID token Google terlalu panjang.');
    }

    /**
     * @param  array<string, mixed>|null  $claims
     */
    private function mockVerifier(?array $claims): void
    {
        $verifier = $this->mock(GoogleIdTokenVerifier::class);
        $verifier->shouldReceive('verify')
            ->with('test-id-token')
            ->andReturn($claims);

        $verifier->shouldReceive('failureReason')
            ->andReturn('google_signature_or_claims_invalid');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function claims(array $overrides = []): array
    {
        return array_replace([
            'aud' => 'web-client-id',
            'iss' => 'https://accounts.google.com',
            'exp' => time() + 3600,
            'email_verified' => true,
            'email' => 'petani.google@example.test',
            'sub' => 'google-subject-1',
            'name' => 'Petani Google',
        ], $overrides);
    }
}
