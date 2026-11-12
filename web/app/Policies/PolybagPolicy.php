<?php

namespace App\Policies;

use App\Models\Polybag;
use App\Models\User;

class PolybagPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Polybag $polybag): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Polybag $polybag): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, Polybag $polybag): bool
    {
        return $user->role === 'admin';
    }
}
