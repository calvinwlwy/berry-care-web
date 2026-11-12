<?php

namespace App\Policies;

use App\Models\SensorReading;
use App\Models\User;

class SensorReadingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SensorReading $reading): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }
}
