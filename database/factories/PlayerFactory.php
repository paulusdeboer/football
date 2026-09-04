<?php

namespace Database\Factories;

use App\Models\Player;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Player> */
class PlayerFactory extends Factory
{
    protected $model = Player::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'rating' => fake()->numberBetween(600, 1000),
            'type' => fake()->randomElement(['attacker', 'defender', 'both']),
            'user_id' => User::factory(),
        ];
    }
}
