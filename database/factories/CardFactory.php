<?php

namespace Database\Factories;

use App\Models\Card;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Card>
 */
class CardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $what = fake()->words(2, true);

        return [
            'source_type' => 'dump',
            'source_path' => null,
            'what_this_is' => $what,
            'does' => fake()->sentence(),
            'does_not' => fake()->sentence(),
            'steps_on' => 'none',
            'speaker' => 'jordan',
            'private' => true,
            'voice_line' => fake()->sentence(),
            'body' => fake()->paragraph(),
        ];
    }
}
