<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use App\Models\Rating;
use App\Services\RatingCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;

class RatingController extends Controller
{
    public function index(): Response
    {
        $games = Game::with(['teams', 'ratings.ratingPlayer', 'ratings.ratedPlayer'])
            ->orderBy('played_at', 'desc')->get();

        return Inertia::render('Ratings/Index', [
            'games' => $games->map(function (Game $game): array {
                $groups = $game->ratings->groupBy('rating_player_id')->map(function ($ratings): array {
                    return [
                        'rating_player_id' => $ratings->first()->rating_player_id,
                        'rating_player_name' => $ratings->first()->ratingPlayer?->name,
                        'ratings' => $ratings->map(fn ($rating) => [
                            'rated_player_id' => $rating->rated_player_id,
                            'rating_value' => $rating->rating_value,
                        ])->values()->all(),
                    ];
                })->values()->all();

                return [
                    'id' => $game->id,
                    'played_at' => $game->played_at,
                    'players' => $game->teams->unique('id')->sortBy('name')->values()
                        ->map(fn ($player) => ['id' => $player->id, 'name' => $player->name])->all(),
                    'ratingsByPlayer' => $groups,
                ];
            })->values()->all(),
        ]);
    }

    public function showForm(Game $game, Player $player): Response
    {
        return Inertia::render('Ratings/Form', [
            'game' => [
                'id' => $game->id,
                'played_at' => $game->played_at,
                'team1_score' => $game->team1_score,
                'team2_score' => $game->team2_score,
            ],
            'player' => ['id' => $player->id, 'name' => $player->name],
            'team1Players' => $game->teams()->where('team', 'team1')->orderBy('name')->get(['players.id', 'players.name']),
            'team2Players' => $game->teams()->where('team', 'team2')->orderBy('name')->get(['players.id', 'players.name']),
            'hasRated' => $game->ratings()->where('rating_player_id', $player->id)->exists(),
            'storeUrl' => URL::temporarySignedRoute(
                'ratings.store', now()->addHours(72), ['game' => $game->id, 'player' => $player->id]
            ),
        ]);
    }

    public function store(Request $request, Game $game, Player $player): RedirectResponse
    {
        $request->validate([
            'ratings' => ['required', 'array'],
            'ratings.*' => ['required', 'numeric', 'min:0', 'max:10'],
        ]);

        if ($game->ratings()->where('rating_player_id', $player->id)->exists()) {
            return back()->withErrors(['rating' => 'You have already submitted a rating for this game.']);
        }

        foreach ($request->ratings as $ratedPlayerId => $ratingValue) {
            Rating::create([
                'game_id' => $game->id,
                'rated_player_id' => $ratedPlayerId,
                'rating_player_id' => $player->id,
                'rating_value' => $ratingValue,
            ]);
        }

        foreach (app(RatingCalculator::class)->calculate($game) as $playerId => $newRating) {
            Player::whereKey($playerId)->update(['rating' => $newRating]);
        }

        $confirmationUrl = URL::temporarySignedRoute(
            'ratings.confirm', now()->addHours(72), ['game' => $game->id, 'player' => $player->id]
        );

        return redirect($confirmationUrl)->with('success', __('Player ratings have been submitted.'));
    }

    public function showConfirmation(Game $game, Player $player): Response
    {
        return Inertia::render('Ratings/Confirmation', [
            'game' => ['id' => $game->id, 'played_at' => $game->played_at],
            'player' => ['id' => $player->id, 'name' => $player->name],
        ]);
    }
}
