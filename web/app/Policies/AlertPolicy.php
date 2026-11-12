<?php

namespace App\Policies;

use App\Models\Alert;
use App\Models\User;

class AlertPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Alert $alert): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function resolve(User $user, Alert $alert): bool
    {
        return true;
    }

    public function delete(User $user, Alert $alert): bool
    {
        return $user->role === 'admin';
    }
}
