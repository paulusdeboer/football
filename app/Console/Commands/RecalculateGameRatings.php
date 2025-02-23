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
    protected $signature = 'ratings:recalculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate player ratings for last game';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $game = Game::whereNotNull('team1_score')
            ->whereNotNull('team2_score')
            ->orderBy('updated_at', 'desc')
            ->first();

        if (!$game) {
            $this->error(__('No recent game found with a result.'));
            return;
        }

        $this->info(__('Recalculating ratings for game ID: ') . $game->id);

        $calculator = new RatingCalculator();
        $newRatings = $calculator->calculate($game);

        foreach ($newRatings as $playerId => $newRating) {
            Player::where('id', $playerId)->update(['rating' => $newRating]);
        }

        $this->info(__('Ratings successfully updated!'));
    }
}
