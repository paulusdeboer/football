<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\RatingRequest;
use App\Services\RatingRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RatingRequestController extends Controller
{
    public function resend(Request $request, Game $game, RatingRequest $ratingRequest, RatingRequestService $service): RedirectResponse
    {
        $this->ensureBelongsToGame($game, $ratingRequest);
        $service->resend($ratingRequest, $request->user()->id);

        return redirect()->route('games.show', $game)->with('success', __('Rating request resent successfully.'));
    }

    public function replace(Request $request, Game $game, RatingRequest $ratingRequest, RatingRequestService $service): RedirectResponse
    {
        $this->ensureBelongsToGame($game, $ratingRequest);
        $data = $request->validate([
            'player_id' => ['nullable', 'integer'],
        ]);

        $service->replace($ratingRequest, $data['player_id'] ?? null, $request->user()->id);

        return redirect()->route('games.show', $game)->with('success', __('Rating request replaced successfully.'));
    }

    private function ensureBelongsToGame(Game $game, RatingRequest $ratingRequest): void
    {
        abort_unless((int) $ratingRequest->game_id === (int) $game->id, 404);
    }
}
