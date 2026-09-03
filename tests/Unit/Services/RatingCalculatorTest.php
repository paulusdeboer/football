<?php

namespace Tests\Unit\Services;

use App\Models\Game;
use App\Models\Player;
use App\Models\Rating;
use App\Services\RatingCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RatingCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_tied_game_uses_the_snapshot_and_average_rating_weights(): void
    {
        $players = Player::factory()->count(2)->create(['rating' => 700]);
        $game = Game::factory()->completed(1, 1)->create();
        $game->teams()->attach([
            $players[0]->id => ['team' => 'team1'],
            $players[1]->id => ['team' => 'team2'],
        ]);

        foreach ($players as $player) {
            $game->gamePlayerRatings()->create([
                'player_id' => $player->id,
                'rating' => 700,
                'type' => $player->type,
            ]);
        }

        Rating::create([
            'game_id' => $game->id,
            'rated_player_id' => $players[0]->id,
            'rating_player_id' => $players[1]->id,
            'rating_value' => 8.0,
        ]);
        Rating::create([
            'game_id' => $game->id,
            'rated_player_id' => $players[1]->id,
            'rating_player_id' => $players[0]->id,
            'rating_value' => 8.0,
        ]);

        $ratings = app(RatingCalculator::class)->calculate($game->fresh());

        $this->assertEqualsWithDelta(725, $ratings[$players[0]->id], 0.0001);
        $this->assertEqualsWithDelta(725, $ratings[$players[1]->id], 0.0001);
    }
}
