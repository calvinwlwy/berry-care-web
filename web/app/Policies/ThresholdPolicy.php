<?php

namespace App\Policies;

use App\Models\Threshold;
use App\Models\User;

class ThresholdPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Threshold $threshold): bool
    {
        return true;
    }

    public function update(User $user): bool
    {
        return $user->role === 'admin';
    }
}
