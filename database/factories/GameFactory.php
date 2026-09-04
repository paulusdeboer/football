<?php

namespace Database\Factories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Game> */
class GameFactory extends Factory
{
    protected $model = Game::class;

    public function definition(): array
    {
        return [
            'played_at' => now()->subDay(),
            'team1_score' => null,
            'team2_score' => null,
        ];
    }

    public function completed(int $team1Score = 3, int $team2Score = 2): static
    {
        return $this->state(fn () => [
            'team1_score' => $team1Score,
            'team2_score' => $team2Score,
        ]);
    }
}
