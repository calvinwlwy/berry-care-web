<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Tests\TestCase;

class GoogleSsoTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_with_verified_google_email_is_linked_and_logged_in(): void
    {
        Log::spy();
        $user = User::factory()->create([
            'email' => 'admin@example.test',
            'role' => 'admin',
        ]);
        $sessionId = session()->getId();
        $this->mockGoogleUser($this->googleUser('google-admin-1', 'admin@example.test'));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($sessionId, session()->getId());
        $this->assertSame('google-admin-1', $user->fresh()->google_id);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        Log::shouldHaveReceived('info')->withArgs(fn (string $message, array $context): bool => $message === 'Google SSO attempt'
            && ($context['outcome'] ?? null) === 'success'
            && ($context['email'] ?? null) === 'ad***@example.test'
            && ! array_key_exists('token', $context)
            && ! array_key_exists('refresh_token', $context)
        );
    }

    public function test_subsequent_login_matches_google_id(): void
    {
        $user = User::factory()->create([
            'email' => 'previous@example.test',
            'google_id' => 'google-existing-id',
            'role' => 'admin',
        ]);
        $this->mockGoogleUser($this->googleUser('google-existing-id', 'changed@example.test'));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseCount('users', 1);
        $this->assertSame('previous@example.test', $user->fresh()->email);
    }

    public function test_unregistered_google_email_is_rejected_without_creating_an_account(): void
    {
        $this->mockGoogleUser($this->googleUser('unknown-google-id', 'unknown@example.test'));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'Akun Google ini tidak memiliki akses.']);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_non_admin_account_is_rejected(): void
    {
        $user = User::factory()->create([
            'email' => 'farmer@example.test',
            'role' => 'petani',
        ]);
        $this->mockGoogleUser($this->googleUser('google-farmer-id', $user->email));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'Akun Google ini tidak memiliki akses.']);

        $this->assertGuest();
        $this->assertNull($user->fresh()->google_id);
    }

    public function test_unverified_google_email_is_rejected(): void
    {
        $user = User::factory()->create([
            'email' => 'unverified@example.test',
            'role' => 'admin',
        ]);
        $this->mockGoogleUser($this->googleUser(
            'google-unverified-id',
            $user->email,
            verified: false
        ));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'Akun Google ini tidak memiliki akses.']);

        $this->assertGuest();
        $this->assertNull($user->fresh()->google_id);
    }

    public function test_google_id_already_linked_to_another_account_is_rejected(): void
    {
        $user = User::factory()->create([
            'email' => 'matching@example.test',
            'google_id' => 'another-google-id',
            'role' => 'admin',
        ]);
        $this->mockGoogleUser($this->googleUser('new-google-id', $user->email));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'Akun Google ini tidak memiliki akses.']);

        $this->assertGuest();
        $this->assertSame('another-google-id', $user->fresh()->google_id);
    }

    public function test_google_email_outside_the_configured_domain_is_rejected(): void
    {
        config(['services.google.allowed_domain' => 'berry.test']);
        $user = User::factory()->create([
            'email' => 'admin@example.test',
            'role' => 'admin',
        ]);
        $this->mockGoogleUser($this->googleUser('external-google-id', $user->email));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'Akun Google ini tidak memiliki akses.']);

        $this->assertGuest();
        $this->assertNull($user->fresh()->google_id);
    }

    public function test_user_denial_returns_a_friendly_login_error(): void
    {
        Log::spy();

        $this->get(route('auth.google.callback', ['error' => 'access_denied']))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'Anda membatalkan proses masuk dengan Google.']);

        $this->assertGuest();
        Log::shouldHaveReceived('warning')->withArgs(fn (string $message, array $context): bool => $message === 'Google SSO attempt'
            && ($context['outcome'] ?? null) === 'rejected'
            && ($context['reason'] ?? null) === 'user_denied'
            && ($context['email'] ?? null) === '***'
        );
    }

    public function test_invalid_oauth_state_returns_a_friendly_error_without_a_server_error(): void
    {
        Log::spy();
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('enablePKCE')->once()->andReturnSelf();
        $provider->shouldReceive('user')->once()->andThrow(new InvalidStateException);
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors([
                'email' => 'Sesi masuk dengan Google tidak valid. Silakan coba lagi.',
            ]);

        $this->assertGuest();
        Log::shouldHaveReceived('warning')->withArgs(fn (string $message, array $context): bool => $message === 'Google SSO attempt'
            && ($context['reason'] ?? null) === 'invalid_state'
            && ($context['exception'] ?? null) === InvalidStateException::class
        );
    }

    public function test_other_socialite_errors_return_a_friendly_message_and_are_logged(): void
    {
        Log::spy();
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('enablePKCE')->once()->andReturnSelf();
        $provider->shouldReceive('user')->once()->andThrow(new \RuntimeException('provider detail'));
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors([
                'email' => 'Login dengan Google gagal. Silakan coba lagi.',
            ]);

        $this->assertGuest();
        Log::shouldHaveReceived('warning')->withArgs(fn (string $message, array $context): bool => $message === 'Google SSO attempt'
            && ($context['detail'] ?? null) === 'provider detail'
        );
    }

    public function test_google_redirect_requests_scopes_account_selection_and_pkce(): void
    {
        config([
            'services.google.client_id' => 'test-client-id',
            'services.google.client_secret' => 'test-client-secret',
            'services.google.redirect' => 'http://localhost/auth/google/callback',
        ]);

        $response = $this->get(route('auth.google'));
        $response->assertRedirect();
        $query = [];
        parse_str((string) parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);

        $this->assertSame('select_account', $query['prompt'] ?? null);
        $this->assertSame('openid profile email', $query['scope'] ?? null);
        $this->assertSame('S256', $query['code_challenge_method'] ?? null);
        $this->assertNotEmpty($query['code_challenge'] ?? null);
        $this->assertNotEmpty($query['state'] ?? null);
        $this->assertNotEmpty(session('state'));
        $this->assertNotEmpty(session('code_verifier'));
    }

    public function test_authenticated_users_cannot_start_or_complete_google_login(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        $this->get(route('auth.google'))->assertRedirect(route('dashboard'));
        $this->get(route('auth.google.callback'))->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_google_routes_are_throttled_to_ten_requests_per_minute(): void
    {
        foreach (['auth.google', 'auth.google.callback'] as $routeName) {
            $this->assertContains(
                'throttle:10,1',
                app('router')->getRoutes()->getByName($routeName)->gatherMiddleware()
            );
        }
    }

    private function mockGoogleUser(GoogleUser $googleUser): void
    {
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('enablePKCE')->once()->andReturnSelf();
        $provider->shouldReceive('user')->once()->andReturn($googleUser);
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);
    }

    private function googleUser(
        string $googleId,
        string $email,
        bool $verified = true
    ): GoogleUser {
        return GoogleUser::fake([
            'id' => $googleId,
            'email' => $email,
            'email_verified' => $verified,
        ]);
    }
}
