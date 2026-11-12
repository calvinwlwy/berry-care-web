<?php

namespace Database\Factories;

use App\Models\Threshold;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Threshold> */
class ThresholdFactory extends Factory
{
    protected $model = Threshold::class;

    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2, '_'),
            'label' => fake()->words(2, true),
            'value' => fake()->randomFloat(2, 1, 100),
            'unit' => '%',
        ];
    }
}
