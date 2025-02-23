<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Game;
use App\Models\Player;
use App\Services\RatingCalculator;

class RecalculateGameRatings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ratings:recalculate {game_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate player ratings for a specific game';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $gameId = $this->argument('game_id');
        $game = Game::with(['teams', 'ratings'])->find($gameId);

        if (!$game) {
            $this->error(__('Game not found.'));
            return;
        }

        $this->info(__('Recalculating ratings for game ID: ') . $gameId);

        $calculator = new RatingCalculator();
        $newRatings = $calculator->calculate($game);

        foreach ($newRatings as $playerId => $newRating) {
            Player::where('id', $playerId)->update(['rating' => $newRating]);
        }

        $this->info(__('Ratings successfully updated!'));
    }
}
