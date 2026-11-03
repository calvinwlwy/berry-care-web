<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HapusAkunUjiCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_deletes_matching_non_admin_accounts_and_their_tokens(): void
    {
        $testUser = User::factory()->create(['email' => 'uji_abc123@contoh.com']);
        $testToken = $testUser->createToken('test');
        $admin = User::factory()->create([
            'email' => 'uji_admin@contoh.com',
            'role' => 'admin',
        ]);
        $adminToken = $admin->createToken('admin');
        User::factory()->create(['email' => 'not-a-test@example.com']);

        $this->artisan('app:hapus-akun-uji')
            ->expectsOutputToContain('Akun yang cocok: 2.')
            ->expectsOutputToContain('Akun non-admin yang akan dihapus: 1.')
            ->expectsOutputToContain('Akun admin dilindungi dan tidak akan dihapus: 1.')
            ->expectsConfirmation('Hapus semua akun non-admin yang cocok beserta tokennya?', 'yes')
            ->expectsOutputToContain('Berhasil menghapus 1 akun uji.')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('users', ['id' => $testUser->id]);
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin']);
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $testToken->accessToken->id]);
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $adminToken->accessToken->id]);
    }

    public function test_command_can_use_custom_pattern_and_cancel_safely(): void
    {
        User::factory()->create(['email' => 'temporary@example.test']);

        $this->artisan('app:hapus-akun-uji', ['--pola' => 'temporary%@example.test'])
            ->expectsOutputToContain('Akun yang cocok: 1.')
            ->expectsConfirmation('Hapus semua akun non-admin yang cocok beserta tokennya?', 'no')
            ->expectsOutputToContain('Penghapusan dibatalkan.')
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', ['email' => 'temporary@example.test']);
    }

    public function test_command_refuses_to_run_in_production(): void
    {
        app()->detectEnvironment(fn (): string => 'production');

        $this->artisan('app:hapus-akun-uji')
            ->expectsOutputToContain('Perintah ini tidak dapat dijalankan di production.')
            ->assertExitCode(1);
    }
}
