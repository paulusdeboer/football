<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GamePlayerRating;
use App\Models\Player;
use App\Services\RatingCalculator;
use App\Services\RatingRequestService;
use App\Services\TeamBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GameController extends Controller
{
    public function index(Request $request): Response
    {
        $sortBy = $request->get('sort_by', 'played_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $allowedSortColumns = ['played_at', 'team1_score', 'team2_score'];

        if (! in_array($sortBy, $allowedSortColumns, true)) {
            $sortBy = 'played_at';
        }
        if (! in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = 'desc';
        }

        return Inertia::render('Games/Index', [
            'games' => Game::query()->orderBy($sortBy, $sortDirection)->get(),
            'sortBy' => $sortBy,
            'sortDirection' => $sortDirection,
            'latestCompletedGameId' => $this->latestCompletedGameId(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Games/Form', [
            'mode' => 'create',
            'game' => null,
            'players' => Player::orderBy('name')->get(['id', 'name']),
            'selectedPlayers' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->validateGameRequest($request);
        $game = Game::create(['played_at' => $request->played_at]);
        $players = Player::whereIn('id', $request->players)->get();

        $this->storeGamePlayerRatings($game, $players);
        $this->handleGameTeams($game, $players);

        return redirect()->route('games.show', $game);
    }

    public function show(Game $game): Response
    {
        $ratingRequestService = app(RatingRequestService::class);
        $ratingRequests = $game->ratingRequests()
            ->with(['player.user', 'events.actor', 'events.previousPlayer', 'events.newPlayer'])
            ->get();
        $team1Ratings = $this->ratingsForTeam($game, 'team1');
        $team2Ratings = $this->ratingsForTeam($game, 'team2');

        return Inertia::render('Games/Show', [
            'game' => $this->gameData($game),
            'canEditResult' => $this->canEditResult($game),
            'canManageRatingRequests' => ! $game->isInPast(),
            'givenRatings' => $this->givenRatingsData($game),
            'ratingRequests' => $ratingRequests->map(fn ($request) => $this->ratingRequestData(
                $request,
                $ratingRequestService->replacementCandidates($game, $request),
            ))->values(),
            'team1Rating' => $team1Ratings->sum('rating'),
            'team2Rating' => $team2Ratings->sum('rating'),
            'team1Ratings' => $this->ratingData($team1Ratings),
            'team2Ratings' => $this->ratingData($team2Ratings),
        ]);
    }

    public function edit(Game $game): Response
    {
        if ($this->hasResult($game)) {
            abort(403, __('A game with a result can no longer be edited.'));
        }

        return Inertia::render('Games/Form', [
            'mode' => 'edit',
            'game' => $this->gameData($game),
            'players' => Player::orderBy('name')->get(['id', 'name']),
            'selectedPlayers' => $game->teams->pluck('id')->values(),
        ]);
    }

    public function update(Request $request, Game $game): RedirectResponse
    {
        if ($this->hasResult($game)) {
            return redirect()->route('games.show', $game)
                ->with('error', __('A game with a result can no longer be edited.'));
        }

        $this->validateGameRequest($request);
        $game->update(['played_at' => $request->played_at]);
        $players = Player::whereIn('id', $request->players)->get();

        $game->teams()->detach();
        $game->gamePlayerRatings()->delete();
        $this->storeGamePlayerRatings($game, $players);
        $this->handleGameTeams($game, $players);

        return redirect()->route('games.show', $game)->with('success', __('Game updated successfully.'));
    }

    public function destroy(Game $game): RedirectResponse
    {
        $game->delete();

        return redirect()->route('games.index')->with('success', __('Game deleted successfully.'));
    }

    public function enterResult(Game $game): Response
    {
        if ($game->team1_score !== null && $game->team2_score !== null && ! $this->canEditResult($game)) {
            abort(403, __('Only the most recent game result can be edited.'));
        }

        return Inertia::render('Games/EnterResult', [
            'game' => $this->gameData($game),
            'team1Players' => $game->teams()->where('team', 'team1')->orderBy('name')->get(['players.id', 'players.name']),
            'team2Players' => $game->teams()->where('team', 'team2')->orderBy('name')->get(['players.id', 'players.name']),
            'hasSentRatingRequests' => $game->ratingRequests()->exists(),
        ]);
    }

    public function storeResult(Request $request, Game $game): RedirectResponse
    {
        $request->validate([
            'team1_score' => ['required', 'integer', 'min:0'],
            'team2_score' => ['required', 'integer', 'min:0'],
            'send_rating_requests' => ['nullable', 'boolean'],
        ]);

        if ($game->team1_score !== null && $game->team2_score !== null && ! $this->canEditResult($game)) {
            return redirect()->route('games.show', $game)->with('error', __('Only the most recent game result can be edited.'));
        }

        $game->update([
            'team1_score' => $request->team1_score,
            'team2_score' => $request->team2_score,
        ]);
        $this->updatePlayerRatings($game);

        if ($request->boolean('send_rating_requests') && ! $game->ratingRequests()->exists()) {
            app(RatingRequestService::class)->createInitialRequests($game);
        }

        return redirect()->route('games.show', $game);
    }

    private function validateGameRequest(Request $request): void
    {
        $request->validate([
            'players' => 'required|array|min:10|max:12',
            'players.*' => 'exists:players,id',
            'played_at' => 'required|date',
        ], [
            'players.required' => __('You must select players for the game'),
            'players.min' => __('At least 10 players are required'),
            'players.max' => __('Maximum 12 players allowed'),
            'players.*.exists' => __('Some of the selected players are invalid'),
            'played_at.required' => __('The game date is required'),
            'played_at.date' => __('The game date must be a valid date'),
        ]);
    }

    private function handleGameTeams(Game $game, $players): void
    {
        $teams = app(TeamBuilder::class)->build($players);
        $syncData = [];
        foreach ($teams as $team => $teamPlayers) {
            foreach ($teamPlayers as $player) {
                $syncData[$player->id] = ['team' => $team];
            }
        }
        $game->teams()->sync($syncData);
    }

    private function storeGamePlayerRatings(Game $game, $players): void
    {
        foreach ($players as $player) {
            GamePlayerRating::create([
                'game_id' => $game->id,
                'player_id' => $player->id,
                'rating' => $player->rating,
                'type' => $player->type,
            ]);
        }
    }

    private function updatePlayerRatings(Game $game): void
    {
        $newRatings = app(RatingCalculator::class)->calculate($game);
        foreach ($newRatings as $playerId => $newRating) {
            Player::whereKey($playerId)->update(['rating' => $newRating]);
        }
    }

    private function latestCompletedGameId(): ?int
    {
        return Game::query()
            ->whereNotNull('team1_score')
            ->whereNotNull('team2_score')
            ->orderByDesc('played_at')
            ->orderByDesc('id')
            ->value('id');
    }

    private function canEditResult(Game $game): bool
    {
        return (int) $this->latestCompletedGameId() === (int) $game->id;
    }

    private function hasResult(Game $game): bool
    {
        return $game->team1_score !== null && $game->team2_score !== null;
    }

    private function ratingRequestData($request, $replacementCandidates): array
    {
        $events = $request->events;
        $latestSendEvent = $events->first(fn ($event) => in_array($event->type, ['initial_send', 'resend'], true));
        $history = $events
            ->reject(fn ($event) => $latestSendEvent && $event->id === $latestSendEvent->id)
            ->values();

        return [
            'id' => $request->id,
            'player_id' => $request->player_id,
            'player_name' => $request->player?->name,
            'player_email' => $request->player?->user?->email,
            'status' => $request->displayStatus(),
            'sent_at' => $request->sent_at?->toIso8601String(),
            'expires_at' => $request->expires_at?->toIso8601String(),
            'completed_at' => $request->completed_at?->toIso8601String(),
            'revoked_at' => $request->revoked_at?->toIso8601String(),
            'replacement_players' => $replacementCandidates->map(fn ($player) => [
                'id' => $player->id,
                'name' => $player->name,
            ])->values()->all(),
            'history' => $history->map(fn ($event) => [
                'type' => $event->type,
                'actor_name' => $event->actor?->name,
                'previous_player_name' => $event->previousPlayer?->name,
                'new_player_name' => $event->newPlayer?->name,
                'expires_at' => $event->expires_at?->toIso8601String(),
                'details' => $event->type === 'send_failed' ? $event->details : null,
                'created_at' => $event->created_at?->toIso8601String(),
            ])->values()->all(),
        ];
    }

    private function ratingsForTeam(Game $game, string $team)
    {
        return $game->gamePlayerRatings()
            ->whereHas('player', fn ($query) => $query->whereHas('teams', fn ($subQuery) => $subQuery
                ->where('game_id', $game->id)->where('team', $team)))
            ->with('player')->get();
    }

    private function gameData(Game $game): array
    {
        return [
            'id' => $game->id,
            'played_at' => $game->played_at,
            'team1_score' => $game->team1_score,
            'team2_score' => $game->team2_score,
        ];
    }

    private function ratingData($ratings): array
    {
        return $ratings->map(fn ($rating) => [
            'id' => $rating->id,
            'type' => $rating->type,
            'rating' => $rating->rating,
            'player' => [
                'id' => $rating->player?->id,
                'name' => $rating->player?->name,
            ],
        ])->values()->all();
    }

    private function givenRatingsData(Game $game): array
    {
        $ratings = $game->ratings()->with(['ratingPlayer', 'ratedPlayer'])->get();

        return [
            'players' => $game->teams->unique('id')->sortBy('name')->values()
                ->map(fn ($player) => ['id' => $player->id, 'name' => $player->name])->all(),
            'ratingsByPlayer' => $ratings->groupBy('rating_player_id')->map(function ($playerRatings): array {
                return [
                    'rating_player_id' => $playerRatings->first()->rating_player_id,
                    'rating_player_name' => $playerRatings->first()->ratingPlayer?->name,
                    'ratings' => $playerRatings->map(fn ($rating) => [
                        'rated_player_id' => $rating->rated_player_id,
                        'rating_value' => $rating->rating_value,
                    ])->values()->all(),
                ];
            })->values()->all(),
        ];
    }
}
