<?php

namespace Database\Factories;

use App\Models\Polybag;
use App\Models\SensorReading;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SensorReading> */
class SensorReadingFactory extends Factory
{
    protected $model = SensorReading::class;

    public function definition(): array
    {
        return [
            'polybag_id' => Polybag::factory(),
            'soil_moisture' => fake()->randomFloat(2, 20, 90),
            'nitrogen' => fake()->randomFloat(2, 10, 80),
            'phosphorus' => fake()->randomFloat(2, 5, 50),
            'potassium' => fake()->randomFloat(2, 50, 250),
            'recorded_at' => now(),
        ];
    }
}
