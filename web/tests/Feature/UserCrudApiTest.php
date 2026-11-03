<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCrudApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_crud_requires_authentication_and_admin_role(): void
    {
        $this->getJson('/api/getuser')
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Tidak terautentikasi.']);

        $petani = User::factory()->create(['role' => 'petani']);
        $token = $petani->createToken('mobile')->plainTextToken;

        $this->withToken($token)->getJson('/api/getuser')
            ->assertForbidden()
            ->assertExactJson(['message' => 'Akses ditolak.']);
    }

    public function test_admin_can_list_users_without_sensitive_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'petani']);
        $token = $admin->createToken('mobile')->plainTextToken;

        $this->withToken($token)->getJson('/api/getuser')
            ->assertOk()
            ->assertJsonPath('message', 'Daftar pengguna berhasil diambil.')
            ->assertJsonPath('data.0.id', $admin->id)
            ->assertJsonPath('data.1.id', $user->id)
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => ['id', 'name', 'email', 'role', 'created_at'],
                ],
            ])
            ->assertJsonMissingPath('data.0.password')
            ->assertJsonMissingPath('data.0.remember_token');
    }

    public function test_admin_can_add_user_and_request_cannot_set_role(): void
    {
        $token = User::factory()->create(['role' => 'admin'])
            ->createToken('mobile')->plainTextToken;

        $this->withToken($token)->postJson('/api/adduser', [
            'name' => 'Petani Baru',
            'email' => 'baru@example.test',
            'password' => 'password123',
            'role' => 'admin',
        ])->assertCreated()
            ->assertJsonPath('message', 'Pengguna berhasil ditambahkan.')
            ->assertJsonPath('data.name', 'Petani Baru')
            ->assertJsonPath('data.email', 'baru@example.test')
            ->assertJsonPath('data.role', 'petani')
            ->assertJsonMissingPath('data.password')
            ->assertJsonMissingPath('data.remember_token');

        $user = User::where('email', 'baru@example.test')->firstOrFail();
        $this->assertSame('petani', $user->role);
        $this->assertTrue(password_verify('password123', $user->password));
    }

    public function test_add_user_accepts_query_parameters_and_returns_validation_errors(): void
    {
        $token = User::factory()->create(['role' => 'admin'])
            ->createToken('mobile')->plainTextToken;

        $this->withToken($token)->postJson(
            '/api/adduser?name=Query%20User&email=query%40example.test&password=password123'
        )->assertCreated()
            ->assertJsonPath('data.name', 'Query User');

        $this->withToken($token)->postJson('/api/adduser', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Data yang diberikan tidak valid.')
            ->assertJsonPath('errors.name.0', 'Nama wajib diisi.')
            ->assertJsonPath('errors.email.0', 'Format email tidak valid.')
            ->assertJsonPath('errors.password.0', 'Password minimal 8 karakter.');
    }

    public function test_add_user_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.test']);
        $token = User::factory()->create(['role' => 'admin'])
            ->createToken('mobile')->plainTextToken;

        $this->withToken($token)->postJson('/api/adduser', [
            'name' => 'Duplicate',
            'email' => 'existing@example.test',
            'password' => 'password123',
        ])->assertUnprocessable()
            ->assertJsonPath('errors.email.0', 'Email sudah terdaftar.');
    }

    public function test_add_user_rejects_password_beyond_bcrypt_byte_limit(): void
    {
        $token = User::factory()->create(['role' => 'admin'])
            ->createToken('mobile')->plainTextToken;

        $this->withToken($token)->postJson('/api/adduser', [
            'name' => 'Long Password',
            'email' => 'long-password@example.test',
            'password' => str_repeat('x', 73),
        ])->assertUnprocessable()
            ->assertJsonPath(
                'errors.password.0',
                'Password maksimal 72 byte agar bcrypt tidak mengabaikan karakter setelah batasnya.'
            );
    }

    public function test_admin_can_update_user_and_password_is_optional(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create([
            'name' => 'Sebelum',
            'email' => 'before@example.test',
            'role' => 'petani',
        ]);
        $token = $admin->createToken('mobile')->plainTextToken;

        $this->withToken($token)->putJson("/api/updateuser/{$user->id}", [
            'name' => 'Sesudah',
            'email' => 'after@example.test',
            'role' => 'admin',
        ])->assertOk()
            ->assertJsonPath('message', 'Pengguna berhasil diperbarui.')
            ->assertJsonPath('data.name', 'Sesudah')
            ->assertJsonPath('data.email', 'after@example.test')
            ->assertJsonPath('data.role', 'petani');

        $this->withToken($token)->putJson("/api/updateuser/{$user->id}", [
            'password' => 'updated123',
        ])->assertOk();

        $user->refresh();
        $this->assertTrue(password_verify('updated123', $user->password));
    }

    public function test_update_user_enforces_unique_email_and_password_length(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['email' => 'current@example.test']);
        User::factory()->create(['email' => 'taken@example.test']);
        $token = $admin->createToken('mobile')->plainTextToken;

        $this->withToken($token)->putJson("/api/updateuser/{$user->id}", [
            'email' => 'taken@example.test',
        ])->assertUnprocessable()
            ->assertJsonPath('errors.email.0', 'Email sudah terdaftar.');

        $this->withToken($token)->putJson("/api/updateuser/{$user->id}", [
            'password' => 'short',
        ])->assertUnprocessable()
            ->assertJsonPath('errors.password.0', 'Password minimal 8 karakter.');
    }

    public function test_update_user_returns_not_found_for_missing_id(): void
    {
        $token = User::factory()->create(['role' => 'admin'])
            ->createToken('mobile')->plainTextToken;

        $this->withToken($token)->putJson('/api/updateuser/999999', [
            'name' => 'Missing',
        ])->assertNotFound()
            ->assertExactJson(['message' => 'Pengguna tidak ditemukan.']);
    }

    public function test_admin_can_delete_user_and_all_their_tokens(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $user->createToken('first');
        $user->createToken('second');
        $token = $admin->createToken('mobile')->plainTextToken;

        $this->withToken($token)->deleteJson("/api/deleteuser/{$user->id}")
            ->assertOk()
            ->assertExactJson(['message' => 'Pengguna berhasil dihapus.']);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_admin_cannot_delete_self_and_missing_user_returns_404(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('mobile')->plainTextToken;

        $this->withToken($token)->deleteJson("/api/deleteuser/{$admin->id}")
            ->assertUnprocessable()
            ->assertExactJson([
                'message' => 'Akun admin yang sedang digunakan tidak dapat dihapus.',
            ]);

        $this->withToken($token)->deleteJson('/api/deleteuser/999999')
            ->assertNotFound()
            ->assertExactJson(['message' => 'Pengguna tidak ditemukan.']);
    }
}
