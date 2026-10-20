<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_available_to_guests_and_has_empty_credentials(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('name="email"', false)
            ->assertSee('value="" autocomplete="username"', false)
            ->assertSee('name="password"', false)
            ->assertDontSee('admin@haifresh.id')
            ->assertDontSee('berrycare2026');
    }

    public function test_user_can_log_in_and_session_id_is_regenerated(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $sessionId = session()->getId();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
            'remember' => '1',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($sessionId, session()->getId());
        $this->assertNotEmpty($user->fresh()->getRememberToken());
    }

    public function test_invalid_login_uses_generic_error_message(): void
    {
        $user = User::factory()->create();

        $this->from(route('login'))->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'Email atau password salah.']);

        $this->assertGuest();
    }

    public function test_non_admin_user_cannot_log_in_to_the_web_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'petani']);

        $this->from(route('login'))->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'Email atau password salah.']);

        $this->assertGuest();
    }

    public function test_login_validates_required_email_format_and_password(): void
    {
        $this->from(route('login'))->post(route('login.store'), [
            'email' => 'bukan-email',
            'password' => '',
        ])->assertRedirect(route('login'))
            ->assertSessionHasErrors([
                'email' => 'Masukkan alamat email yang valid.',
                'password' => 'Password wajib diisi.',
            ]);
    }

    public function test_login_is_rate_limited_by_email_and_ip(): void
    {
        $email = 'unknown@example.test';
        $key = mb_strtolower($email).'|127.0.0.1';
        RateLimiter::clear($key);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.store'), [
                'email' => $email,
                'password' => 'wrong-password',
            ])->assertSessionHasErrors(['email' => 'Email atau password salah.']);
        }

        $this->post(route('login.store'), [
            'email' => $email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertStringContainsString(
            'Terlalu banyak percobaan login.',
            session('errors')->first('email')
        );

        RateLimiter::clear($key);
    }

    public function test_dashboard_redirects_guest_to_login(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_dashboard_has_shared_logout_confirmation_modal(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('open-logout-confirmation', false)
            ->assertSee('role="dialog"', false)
            ->assertSee('aria-modal="true"', false)
            ->assertSee('aria-labelledby="logout-confirmation-title"', false)
            ->assertSee('Keluar dari akun?', false)
            ->assertSee('Sesi Anda akan diakhiri dan Anda perlu login lagi untuk mengakses dashboard.', false)
            ->assertSee('Batal', false)
            ->assertSee('Ya, Keluar', false)
            ->assertSee('Keluar...', false)
            ->assertSee('name="_token"', false);
    }

    public function test_authenticated_user_can_log_out_and_session_is_invalidated(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $token = session()->token();

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertNotSame($token, session()->token());
    }

    public function test_get_logout_does_not_log_out_authenticated_user(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get('/logout')->assertMethodNotAllowed();

        $this->assertAuthenticated();
    }

    public function test_authenticated_user_is_redirected_away_from_guest_pages(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('login'))->assertRedirect(route('dashboard'));
        $this->get(route('password.help'))->assertRedirect(route('dashboard'));
    }

    public function test_password_help_page_tells_user_to_contact_admin(): void
    {
        $this->get(route('password.help'))
            ->assertOk()
            ->assertSee('silakan hubungi admin kebun');
    }
}
