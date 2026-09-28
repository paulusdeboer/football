<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use App\Models\Rating;
use App\Models\RatingRequest;
use App\Services\RatingCalculator;
use App\Services\RatingRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
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

    public function showForm(Request $request, Game $game, RatingRequest $ratingRequest, RatingRequestService $service): Response
    {
        $this->ensureRequestMatchesGame($game, $ratingRequest);
        $service->ensureMatchesVersion($ratingRequest, $request->query('v'));
        $service->ensureViewable($ratingRequest);
        $player = $ratingRequest->player;
        $expectedRatedPlayerIds = $this->expectedRatedPlayerIds($game, $player);
        $existingRatings = $game->ratings()
            ->where('rating_player_id', $player->id)
            ->get()
            ->keyBy('rated_player_id');

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
            'hasRated' => $this->hasCompleteRatingSet($expectedRatedPlayerIds, $existingRatings->keys()->all()),
            'existingRatings' => $existingRatings->mapWithKeys(fn (Rating $rating) => [
                (string) $rating->rated_player_id => $rating->rating_value,
            ]),
            'storeUrl' => $service->signedUrl($ratingRequest, 'ratings.store'),
        ]);
    }

    public function store(Request $request, Game $game, RatingRequest $ratingRequest, RatingRequestService $service): RedirectResponse
    {
        $this->ensureRequestMatchesGame($game, $ratingRequest);
        $service->ensureMatchesVersion($ratingRequest, $request->query('v'));
        $player = $ratingRequest->player;
        $request->validate([
            'ratings' => ['required', 'array'],
            'ratings.*' => ['required', 'numeric', 'min:5', 'max:10'],
        ]);

        $expectedRatedPlayerIds = $this->expectedRatedPlayerIds($game, $player);
        $submittedRatedPlayerIds = collect(array_keys($request->input('ratings', [])))
            ->map(fn ($id) => (string) $id)
            ->all();

        if (array_diff($expectedRatedPlayerIds, $submittedRatedPlayerIds) !== []
            || array_diff($submittedRatedPlayerIds, $expectedRatedPlayerIds) !== []) {
            throw ValidationException::withMessages([
                'ratings' => __('Please rate every player in the game exactly once.'),
            ]);
        }

        $existingRatedPlayerIds = $game->ratings()
            ->where('rating_player_id', $player->id)
            ->pluck('rated_player_id')
            ->all();
        $hasCompleteSubmission = $this->hasCompleteRatingSet($expectedRatedPlayerIds, $existingRatedPlayerIds);

        if ($hasCompleteSubmission) {
            return back()->withErrors(['rating' => 'You have already submitted a rating for this game.']);
        }

        $isRepairingPartialSubmission = $ratingRequest->status === RatingRequest::STATUS_COMPLETED
            && $existingRatedPlayerIds !== [];

        if (! $isRepairingPartialSubmission) {
            $service->ensureUsable($ratingRequest);
        }

        DB::transaction(function () use ($request, $game, $player, $ratingRequest, $service, $expectedRatedPlayerIds): void {
            $ratingRequest->refresh();
            $service->ensureMatchesVersion($ratingRequest, $request->query('v'));

            $existingRatedPlayerIds = $game->ratings()
                ->where('rating_player_id', $player->id)
                ->pluck('rated_player_id')
                ->all();
            $hasCompleteSubmission = $this->hasCompleteRatingSet($expectedRatedPlayerIds, $existingRatedPlayerIds);

            if ($hasCompleteSubmission) {
                throw ValidationException::withMessages(['rating' => 'You have already submitted a rating for this game.']);
            }

            $isRepairingPartialSubmission = $ratingRequest->status === RatingRequest::STATUS_COMPLETED
                && $existingRatedPlayerIds !== [];

            if (! $isRepairingPartialSubmission) {
                $service->ensureUsable($ratingRequest);
            }

            foreach ($request->ratings as $ratedPlayerId => $ratingValue) {
                Rating::updateOrCreate(
                    [
                        'game_id' => $game->id,
                        'rated_player_id' => $ratedPlayerId,
                        'rating_player_id' => $player->id,
                    ],
                    ['rating_value' => $ratingValue],
                );
            }

            foreach (app(RatingCalculator::class)->calculate($game) as $playerId => $newRating) {
                Player::whereKey($playerId)->update(['rating' => $newRating]);
            }

            $service->markCompleted($ratingRequest);
        });

        $confirmationUrl = URL::temporarySignedRoute(
            'ratings.confirm', now()->addHours(72), [
                'game' => $game->id,
                'ratingRequest' => $ratingRequest->id,
                'v' => $ratingRequest->token_version,
            ]
        );

        return redirect($confirmationUrl)->with('success', __('Player ratings have been submitted.'));
    }

    public function showConfirmation(Request $request, Game $game, RatingRequest $ratingRequest, RatingRequestService $service): Response
    {
        $this->ensureRequestMatchesGame($game, $ratingRequest);
        $service->ensureMatchesVersion($ratingRequest, $request->query('v'));

        return Inertia::render('Ratings/Confirmation', [
            'game' => ['id' => $game->id, 'played_at' => $game->played_at],
            'player' => ['id' => $ratingRequest->player_id, 'name' => $ratingRequest->player?->name],
        ]);
    }

    private function ensureRequestMatchesGame(Game $game, RatingRequest $ratingRequest): void
    {
        abort_unless((int) $ratingRequest->game_id === (int) $game->id, 404);
        abort_unless($game->teams()->whereKey($ratingRequest->player_id)->exists(), 404);
    }

    /** @return list<string> */
    private function expectedRatedPlayerIds(Game $game, Player $player): array
    {
        return $game->teams()
            ->pluck('players.id')
            ->reject(fn ($id) => (int) $id === (int) $player->id)
            ->map(fn ($id) => (string) $id)
            ->values()
            ->all();
    }

    /** @param list<int|string> $submittedPlayerIds */
    private function hasCompleteRatingSet(array $expectedPlayerIds, array $submittedPlayerIds): bool
    {
        $submittedPlayerIds = array_map('strval', $submittedPlayerIds);

        return $expectedPlayerIds !== []
            && array_diff($expectedPlayerIds, $submittedPlayerIds) === [];
    }
}
