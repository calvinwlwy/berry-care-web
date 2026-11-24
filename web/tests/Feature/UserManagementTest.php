<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_filter_and_paginate_database_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create([
            'name' => 'Kebun Strawberry Utara',
            'email' => 'utara@example.test',
            'role' => 'petani',
        ]);
        User::factory()->create([
            'name' => 'Kebun Strawberry Selatan',
            'email' => 'selatan@example.test',
            'role' => 'petani',
        ]);
        User::factory()->create(['name' => 'Admin Pendamping', 'role' => 'admin']);
        User::factory()->count(8)->create(['role' => 'petani']);
        $this->actingAs($admin);

        $this->get(route('users.index', ['q' => 'utara', 'role' => 'petani']))
            ->assertOk()
            ->assertSee('utara@example.test')
            ->assertDontSee('selatan@example.test');

        $this->get(route('users.index'))
            ->assertViewHas('users', fn ($users) => $users->perPage() === 10
                && $users->count() === 10
                && $users->total() === 12);

        $this->get(route('users.index', ['page' => 2]))
            ->assertViewHas('users', fn ($users) => $users->count() === 2);
    }

    public function test_admin_can_create_users_with_trimmed_name_and_hashed_password(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->post(route('users.store'), [
            '_form' => 'create',
            'name' => '  Sari Stroberi  ',
            'email' => 'sari@example.test',
            'password' => 'secret-password',
            'role' => 'petani',
        ])->assertRedirect(route('users.index'))
            ->assertSessionHas('status', 'Pengguna baru berhasil ditambahkan.');

        $user = User::query()->where('email', 'sari@example.test')->firstOrFail();
        $this->assertSame('Sari Stroberi', $user->name);
        $this->assertTrue(Hash::check('secret-password', $user->password));
        $this->assertNotSame('secret-password', $user->password);
    }

    public function test_user_creation_validates_required_fields_role_duplicate_email_and_bcrypt_limit(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $existing = User::factory()->create(['email' => 'existing@example.test']);

        $this->from(route('users.index'))->post(route('users.store'), [
            '_form' => 'create',
            'name' => '',
            'email' => 'not-an-email',
            'password' => str_repeat('x', 73),
            'role' => 'owner',
        ])->assertRedirect(route('users.index'))
            ->assertSessionHasErrors(['name', 'email', 'password', 'role'])
            ->assertSessionHas('_old_input._form', 'create');

        $this->post(route('users.store'), [
            'name' => 'Nama Baru',
            'email' => $existing->email,
            'password' => 'secret-password',
            'role' => 'petani',
        ])->assertSessionHasErrors('email');
    }

    public function test_admin_can_update_own_email_and_change_password_without_requiring_a_new_password(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Utama',
            'email' => 'admin@example.test',
            'password' => 'old-password',
            'role' => 'admin',
        ]);
        $other = User::factory()->create(['email' => 'other@example.test']);
        $this->actingAs($admin);

        $this->put(route('users.update', $admin), [
            'name' => 'Admin Utama',
            'email' => 'admin@example.test',
            'password' => '',
            'role' => 'admin',
        ])->assertRedirect(route('users.index'));
        $this->assertTrue(Hash::check('old-password', $admin->fresh()->password));

        $this->from(route('users.index'))->put(route('users.update', $admin), [
            '_form' => 'edit',
            'user_id' => $admin->id,
            'name' => 'Admin Utama',
            'email' => $other->email,
            'password' => '',
            'role' => 'admin',
        ])->assertRedirect(route('users.index'))
            ->assertSessionHasErrors('email');

        $token = $admin->createToken('test-token');
        $this->put(route('users.update', $admin), [
            'name' => 'Admin Utama',
            'email' => 'admin@example.test',
            'password' => 'new-password',
            'role' => 'admin',
        ])->assertRedirect(route('users.index'));
        $this->assertTrue(Hash::check('new-password', $admin->fresh()->password));
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->accessToken->id]);
    }

    public function test_admin_can_change_another_user_role_and_revoke_their_tokens(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $target = User::factory()->create(['role' => 'petani']);
        $token = $target->createToken('test-token');

        $this->put(route('users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'password' => '',
            'role' => 'admin',
        ])->assertRedirect(route('users.index'));

        $this->assertSame('admin', $target->fresh()->role);
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->accessToken->id]);
    }

    public function test_admin_can_delete_another_user_and_revoke_all_their_tokens(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $target = User::factory()->create(['role' => 'petani']);
        $token = $target->createToken('test-token');

        $this->delete(route('users.destroy', $target), [
            '_form' => 'delete',
            'user_id' => $target->id,
        ])->assertRedirect(route('users.index'))
            ->assertSessionHas('status', 'Pengguna berhasil dihapus.');

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->accessToken->id]);
    }

    public function test_admin_cannot_delete_or_demote_their_own_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $this->from(route('users.index'))->put(route('users.update', $admin), [
            '_form' => 'edit',
            'user_id' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
            'password' => '',
            'role' => 'petani',
        ])->assertRedirect(route('users.index'))
            ->assertSessionHasErrors('role');
        $this->assertSame('admin', $admin->fresh()->role);

        $this->from(route('users.index'))->delete(route('users.destroy', $admin), [
            '_form' => 'delete',
            'user_id' => $admin->id,
        ])->assertRedirect(route('users.index'))
            ->assertSessionHasErrors('delete');
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin']);
    }

    public function test_non_admin_cannot_access_user_management_routes(): void
    {
        $petani = User::factory()->create(['role' => 'petani']);
        $target = User::factory()->create(['role' => 'petani']);
        $this->actingAs($petani);

        $this->get(route('users.index'))->assertForbidden();
        $this->post(route('users.store'), [])->assertForbidden();
        $this->put(route('users.update', $target), [])->assertForbidden();
        $this->delete(route('users.destroy', $target))->assertForbidden();
    }

    public function test_deleting_a_user_removes_any_remaining_sanctum_tokens(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $target = User::factory()->create(['role' => 'petani']);
        $target->createToken('first');
        $target->createToken('second');

        $this->delete(route('users.destroy', $target));

        $this->assertSame(0, PersonalAccessToken::query()->where('tokenable_id', $target->id)->count());
    }
}
