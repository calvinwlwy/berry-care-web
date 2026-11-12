<?php

namespace Database\Factories;

use App\Models\Alert;
use App\Models\Polybag;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Alert> */
class AlertFactory extends Factory
{
    protected $model = Alert::class;

    public function definition(): array
    {
        return [
            'polybag_id' => Polybag::factory(),
            'type' => 'soil_moisture_low',
            'severity' => fake()->randomElement(['info', 'warning', 'critical']),
            'message' => fake()->sentence(),
            'status' => 'active',
            'source' => 'system',
            'resolved_at' => null,
            'resolved_by' => null,
        ];
    }
}
