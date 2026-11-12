<?php

namespace App\Policies;

use App\Models\ClimateReading;
use App\Models\User;

class ClimateReadingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ClimateReading $reading): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }
}
