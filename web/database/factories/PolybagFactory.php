<?php

namespace Database\Factories;

use App\Models\Polybag;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Polybag> */
class PolybagFactory extends Factory
{
    protected $model = Polybag::class;

    public function definition(): array
    {
        return [
            'code' => 'POT-'.fake()->unique()->numerify('###'),
            'block' => fake()->randomElement(['A', 'B', 'C']),
            'location' => fake()->optional()->streetAddress(),
            'is_active' => true,
        ];
    }
}
