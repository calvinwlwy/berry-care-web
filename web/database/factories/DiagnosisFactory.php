<?php

namespace Database\Factories;

use App\Models\Diagnosis;
use App\Models\Polybag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Diagnosis> */
class DiagnosisFactory extends Factory
{
    protected $model = Diagnosis::class;

    public function definition(): array
    {
        return [
            'polybag_id' => Polybag::factory(),
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['leaf', 'fruit']),
            'label' => fake()->randomElement(['Sehat', 'Bercak Daun', 'Buah Matang']),
            'confidence' => fake()->randomFloat(2, 50, 100),
            'severity' => fake()->randomElement(['ok', 'watch', 'action']),
            'notes' => fake()->optional()->sentence(),
            'image_path' => null,
            'scanned_at' => now(),
        ];
    }
}
