<?php

namespace Database\Seeders;

use App\Models\Rating;
use App\Models\Player;
use App\Models\Game;
use Database\Seeders\Helper\BaseSeeder;

class RatingSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     */
    public function execute(): void
    {
        $playerIds = Player::pluck('id')->toArray();
        $games = Game::with('teams')->get();

        if (count($playerIds) < 2 || $games->count() === 0) {
            $this->command->info('Not enough players or games to create ratings.');
            return;
        }

        $lastRatingId = Rating::max('id') ?? 0;

        foreach ($games as $game) {
            $playersInGame = $game->teams->pluck('id')->toArray();

            if (count($playersInGame) < 2) {
                continue;
            }

            $ratingPlayers = collect($playersInGame)->random(min(3, count($playersInGame)));

            foreach ($ratingPlayers as $ratingPlayerId) {

                $ratedPlayers = collect($playersInGame)->filter(function ($playerId) use ($ratingPlayerId) {
                    return $playerId !== $ratingPlayerId;
                });

                foreach ($ratedPlayers as $ratedPlayerId) {
                    $this->add([
                        'id' => ++$lastRatingId,
                        'game_id' => $game->id,
                        'rated_player_id' => $ratedPlayerId,
                        'rating_player_id' => $ratingPlayerId,
                        'rating_value' => $this->fakerService->numberBetween(5, 10),
                        'created_at' => $this->fakerService->dateTimeBetween('-1 year'),
                        'updated_at' => $this->fakerService->dateTimeBetween('-1 year'),
                    ], Rating::class);
                }
            }
        }
    }
}
