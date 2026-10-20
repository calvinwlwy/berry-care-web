<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('auth.admin.email');
        $password = config('auth.admin.password');

        if (! is_string($email) || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new RuntimeException('Set ADMIN_EMAIL di .env ke alamat email valid sebelum menjalankan seeder admin.');
        }

        if (! is_string($password) || $password === '') {
            throw new RuntimeException('Set ADMIN_PASSWORD di .env sebelum menjalankan seeder admin.');
        }

        $user = User::firstOrNew(['email' => $email]);
        $user->name = 'Administrator';
        $user->password = $password;
        $user->role = 'admin';
        $user->save();
    }
}
