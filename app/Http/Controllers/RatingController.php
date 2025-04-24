<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RatingController extends Controller
{
    public function index()
    {
        $ratings = Rating::with(['ratedPlayer', 'ratingPlayer', 'game'])->get();
        
        return Inertia::render('Ratings/Index', [
            'ratings' => $ratings
        ]);
    }

    public function create(Game $game)
    {
        $game->load(['teams']);
        $players = $game->teams()->get();
        $currentUser = Auth::user();
        $currentPlayer = Player::where('user_id', $currentUser->id)->first();
        
        return Inertia::render('Ratings/Create', [
            'game' => $game,
            'players' => $players,
            'currentPlayer' => $currentPlayer
        ]);
    }

    public function store(Request $request, Game $game)
    {
        $validated = $request->validate([
            'ratings' => 'required|array',
            'ratings.*.player_id' => 'required|exists:players,id',
            'ratings.*.value' => 'required|integer|min:1|max:10',
        ]);
        
        $currentUser = Auth::user();
        $ratingPlayer = Player::where('user_id', $currentUser->id)->first();
        
        if (!$ratingPlayer) {
            return redirect()->back()->withErrors(['player' => 'You need to create a player profile first']);
        }
        
        foreach ($validated['ratings'] as $rating) {
            // Skip self-rating
            if ($rating['player_id'] == $ratingPlayer->id) {
                continue;
            }
            
            // Check if rating already exists and update it
            $existingRating = Rating::where([
                'game_id' => $game->id,
                'rated_player_id' => $rating['player_id'],
                'rating_player_id' => $ratingPlayer->id
            ])->first();
            
            if ($existingRating) {
                $existingRating->update([
                    'rating_value' => $rating['value']
                ]);
            } else {
                Rating::create([
                    'game_id' => $game->id,
                    'rated_player_id' => $rating['player_id'],
                    'rating_player_id' => $ratingPlayer->id,
                    'rating_value' => $rating['value']
                ]);
            }
        }
        
        return redirect()->route('games.show', $game)
            ->with('message', 'Ratings submitted successfully.');
    }
}