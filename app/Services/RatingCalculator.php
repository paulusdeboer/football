<?php

namespace App\Services;

use App\Models\Game;

class RatingCalculator
{
    public function calculate(Game $game): array
    {
        $newRatings = [];

        $team1Score = $game->team1_score;
        $team2Score = $game->team2_score;
        $scoreDifference = abs($team1Score - $team2Score);

        $playersInGame = $game->teams;

        foreach ($playersInGame as $player) {
            $previousRating = $player->gamePlayerRatings()->where('game_id', $game->id)->pluck('rating')->first();

            $ratings = $game->ratings()->where('rated_player_id', $player->id)->pluck('rating_value');

            $averageRating = $ratings->count() > 0 ? ($ratings->sum() / $ratings->count()) * 100 : $previousRating;

            $team = $player->pivot->team;
            $won = ($team == 'team1' && $team1Score > $team2Score) || ($team == 'team2' && $team2Score > $team1Score);

            $coefficient = $won ?
                1 + (log($scoreDifference + 1) / 250) * $scoreDifference :
                1 - (log($scoreDifference + 1) / 250) * $scoreDifference;

            $newRating = ($previousRating * 0.75) + ($averageRating * 0.25 * $coefficient);

            $newRatings[$player->id] = $newRating;
        }

        return $newRatings;
    }
}
