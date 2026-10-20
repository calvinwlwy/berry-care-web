<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_petani_user_and_returns_a_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Petani Berry',
            'email' => 'petani@example.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => 'admin',
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Registrasi berhasil.')
            ->assertJsonPath('user.name', 'Petani Berry')
            ->assertJsonPath('user.email', 'petani@example.test')
            ->assertJsonPath('user.role', 'petani')
            ->assertJsonMissingPath('user.password')
            ->assertJsonStructure(['token']);

        $user = User::where('email', 'petani@example.test')->firstOrFail();
        $this->assertTrue(password_verify('rahasia123', $user->password));
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'auth_token',
        ]);
    }

    public function test_register_returns_indonesian_validation_errors(): void
    {
        $this->postJson('/api/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Data yang diberikan tidak valid.')
            ->assertJsonPath('errors.name.0', 'Nama wajib diisi.')
            ->assertJsonPath('errors.email.0', 'Format email tidak valid.')
            ->assertJsonPath('errors.password.0', 'Password minimal 8 karakter.');
    }

    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'used@example.test']);

        $this->postJson('/api/register', [
            'name' => 'Another User',
            'email' => 'used@example.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertUnprocessable()
            ->assertJsonPath('errors.email.0', 'Email sudah terdaftar.');
    }

    public function test_api_login_returns_a_bearer_token_for_valid_credentials(): void
    {
        $user = User::factory()->create(['password' => 'rahasia123']);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'rahasia123',
        ])->assertOk()
            ->assertJsonPath('message', 'Login berhasil.')
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.role', 'petani')
            ->assertJsonStructure(['token']);
    }

    public function test_api_login_returns_generic_unauthorized_response(): void
    {
        User::factory()->create(['email' => 'known@example.test']);

        $this->postJson('/api/login', [
            'email' => 'known@example.test',
            'password' => 'wrong-password',
        ])->assertUnauthorized()
            ->assertExactJson(['message' => 'Email atau password salah']);

        $this->postJson('/api/login', [
            'email' => 'unknown@example.test',
            'password' => 'wrong-password',
        ])->assertUnauthorized()
            ->assertExactJson(['message' => 'Email atau password salah']);
    }

    public function test_user_endpoint_requires_a_bearer_token_and_returns_profile(): void
    {
        $this->getJson('/api/user')
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Tidak terautentikasi.']);

        $user = User::factory()->create(['role' => 'petani']);
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/user')
            ->assertOk()
            ->assertExactJson([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => 'petani',
                ],
            ]);
    }

    public function test_logout_deletes_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')
            ->assertOk()
            ->assertExactJson(['message' => 'Logout berhasil.']);

        Auth::forgetGuards();

        $this->withToken($token)->getJson('/api/user')
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Tidak terautentikasi.']);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_registered_api_account_cannot_log_in_to_web_dashboard(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Web Ineligible',
            'email' => 'petani-web@example.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertCreated()
            ->assertJsonPath('user.role', 'petani');

        $this->from(route('login'))->post(route('login.store'), [
            'email' => 'petani-web@example.test',
            'password' => 'rahasia123',
        ])->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'Email atau password salah.']);

        $this->assertGuest();
    }

    public function test_api_login_is_limited_to_five_attempts_per_email_and_ip(): void
    {
        $email = 'limited@example.test';
        RateLimiter::clear(Str::transliterate($email.'|127.0.0.1'));

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/login', [
                'email' => $email,
                'password' => 'wrong-password',
            ])->assertUnauthorized();
        }

        $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'wrong-password',
        ])->assertStatus(429)
            ->assertJsonPath('message', 'Terlalu banyak percobaan login. Silakan coba lagi dalam satu menit.');

        RateLimiter::clear(Str::transliterate($email.'|127.0.0.1'));
    }

    public function test_api_registration_can_be_disabled(): void
    {
        config(['services.api.registration_enabled' => false]);

        $this->postJson('/api/register', [
            'name' => 'Disabled',
            'email' => 'disabled@example.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertForbidden()
            ->assertExactJson(['message' => 'Registrasi API dinonaktifkan.']);

        $this->assertDatabaseMissing('users', ['email' => 'disabled@example.test']);
    }
}
