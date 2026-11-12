<?php

namespace Database\Factories;

use App\Models\ClimateReading;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ClimateReading> */
class ClimateReadingFactory extends Factory
{
    protected $model = ClimateReading::class;

    public function definition(): array
    {
        return [
            'temperature' => fake()->randomFloat(2, 10, 40),
            'humidity' => fake()->randomFloat(2, 30, 95),
            'recorded_at' => now(),
        ];
    }
}
