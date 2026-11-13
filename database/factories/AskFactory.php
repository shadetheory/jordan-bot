<?php

namespace Database\Factories;

use App\Models\Ask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ask>
 */
class AskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source' => 'cli',
            'session_id' => null,
            'status' => 'done',
            'question' => fake()->sentence(),
            'reply' => fake()->sentence(),
        ];
    }
}
