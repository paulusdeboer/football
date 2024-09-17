<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use App\Models\Rating;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function showForm(Game $game, Player $player)
    {
        return view('ratings.form', compact('game', 'player'));
    }

    public function store(Request $request, Game $game, Player $ratedPlayer)
    {
        // Store ratings from the submitted form
        Rating::create([
            'game_id' => $game->id,
            'rated_player_id' => $ratedPlayer->id,
            'rating_player_id' => auth()->id(),
            'rating_value' => $request->rating_value,
        ]);

        // Adjust player rating based on the new rating value
        $this->adjustPlayerRating($ratedPlayer, $request->rating_value);

        return redirect()->route('games.show', $game);
    }

    private function adjustPlayerRating(Player $player, $ratingValue)
    {
        // Logic to adjust the player's rating based on the average score received
    }
}
