<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use App\Models\Rating;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function showForm(Game $game, Player $player)
    {
        $team1Players = $game->teams()->where('team', 'team1')->get()->sortBy('name');
        $team2Players = $game->teams()->where('team', 'team2')->get()->sortBy('name');

        // Check if the player has already submitted a rating for this game
        $hasRated = $game->ratings()->where('rating_player_id', $player->id)->exists();

        return view('ratings.form', compact('game', 'player', 'team1Players', 'team2Players', 'hasRated'));
    }

    public function store(Request $request, Game $game, Player $player)
    {
        $request->validate([
            'ratings' => 'required|array',
            'ratings.*' => 'required|numeric|min:0|max:10',
        ]);

        // Check if the player has already submitted a rating for this game
        $hasRated = $game->ratings()->where('rating_player_id', $player->id)->exists();

        if ($hasRated) {
            return redirect()->back()->withErrors(['rating' => 'You have already submitted a rating for this game.']);
        }

        foreach ($request->ratings as $ratedPlayerId => $ratingValue) {
            Rating::create([
                'game_id' => $game->id,
                'rated_player_id' => $ratedPlayerId,
                'rating_player_id' => $player->id,
                'rating_value' => $ratingValue,
            ]);
        }

        $this->updatePlayerRatings($game);

        $confirmationUrl = URL::temporarySignedRoute(
            'ratings.confirm', now()->addHours(48), ['game' => $game->id, 'player' => $player->id]
        );

        return redirect($confirmationUrl)->with('success', __('Player ratings have been submitted.'));
    }

    public function showConfirmation(Game $game, Player $player)
    {
        return view('ratings.confirmation', compact('game', 'player'));
    }

    public function updatePlayerRatings(Game $game)
    {
        $team1Score = $game->team1_score;
        $team2Score = $game->team2_score;
        $scoreDifference = abs($team1Score - $team2Score);

        $playersInGame = $game->teams;

        foreach ($playersInGame as $player) {
            $previousRating = $player->previous_rating;

            // Check if there are already existing ratings for this player in the game
            $ratings = $game->ratings()->where('rated_player_id', $player->id)->pluck('rating_value');

            $averageRating = $ratings->count() > 0 ? ($ratings->sum() / $ratings->count()) * 100 : 0;

            // Use pivot to determine if the player was in the winning team or not.
            $team = $player->pivot->team;
            $won = ($team == 'team1' && $team1Score > $team2Score) || ($team == 'team2' && $team2Score > $team1Score);

            $coefficient = $won ? 1 + ($scoreDifference / 100) : 1 - ($scoreDifference / 100);

            // Use a weighted average so the new rating won't deviate too much.
            $weightedAverage = ($previousRating * 0.95) + ($averageRating * 0.05);
            $newPlayerRating = $weightedAverage * $coefficient;

            $player->rating = $newPlayerRating;
            $player->save();
        }
    }
}
