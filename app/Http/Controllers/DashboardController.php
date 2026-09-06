<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GamePlayerRating;
use App\Models\Player;
use App\Models\Rating;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    private const PERIODS = ['1', '5', '10', '20', 'all'];

    private const PLAYER_CHART_COLORS = [
        ['background' => '#3678b8', 'border' => '#245d91'],
        ['background' => '#2e9b73', 'border' => '#277f5f'],
        ['background' => '#8b5cf6', 'border' => '#6841c6'],
        ['background' => '#e97817', 'border' => '#b95708'],
        ['background' => '#d64b4b', 'border' => '#a93838'],
        ['background' => '#0f9fa8', 'border' => '#08777e'],
        ['background' => '#c05299', 'border' => '#96366f'],
        ['background' => '#64748b', 'border' => '#475569'],
        ['background' => '#1f77b4', 'border' => '#15527c'],
        ['background' => '#ff7f0e', 'border' => '#b35a08'],
        ['background' => '#2ca02c', 'border' => '#1d6b1d'],
        ['background' => '#d62728', 'border' => '#941b1c'],
        ['background' => '#9467bd', 'border' => '#68458a'],
        ['background' => '#8c564b', 'border' => '#623b34'],
        ['background' => '#e377c2', 'border' => '#a44f8a'],
        ['background' => '#7f7f7f', 'border' => '#595959'],
        ['background' => '#bcbd22', 'border' => '#858619'],
        ['background' => '#17becf', 'border' => '#108690'],
        ['background' => '#f28e2b', 'border' => '#aa611e'],
        ['background' => '#e15759', 'border' => '#9d3d3f'],
    ];

    public function index(Request $request): Response
    {
        $period = (string) $request->input('period', '10');
        $period = in_array($period, self::PERIODS, true) ? $period : '10';

        $requestedPlayerIds = $request->input('player_ids');
        if ($requestedPlayerIds === null && $request->has('player_id')) {
            $requestedPlayerIds = [$request->input('player_id')];
        }
        if (! is_array($requestedPlayerIds)) {
            $requestedPlayerIds = [];
        }

        $players = Player::query()
            ->orderByDesc('rating')
            ->orderBy('name')
            ->get(['id', 'name', 'rating']);
        $selectedPlayerIds = collect($requestedPlayerIds)
            ->filter(fn ($id): bool => is_numeric($id) && (int) $id > 0)
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->filter(fn (int $id): bool => $players->contains('id', $id))
            ->values();

        $completedGames = Game::query()
            ->whereNotNull('team1_score')
            ->whereNotNull('team2_score')
            ->with(['teams', 'ratingRequests'])
            ->orderByDesc('played_at')
            ->orderByDesc('id')
            ->get();

        $selectedGames = ($period === 'all' ? $completedGames : $completedGames->take((int) $period))
            ->sortBy(fn (Game $game): string => sprintf('%s-%010d', (string) $game->played_at, $game->id))
            ->values();
        $gameIds = $selectedGames->pluck('id');
        $gamePositions = $selectedGames->mapWithKeys(fn (Game $game, int $position): array => [$game->id => $position]);

        $snapshots = GamePlayerRating::query()
            ->whereIn('game_id', $gameIds)
            ->with('player')
            ->get();
        $snapshotsByGame = $snapshots->groupBy('game_id');
        $snapshotsByPlayer = $snapshots->groupBy('player_id');

        $ratings = Rating::query()
            ->whereIn('game_id', $gameIds)
            ->get();
        $ratingsByPlayer = $ratings->groupBy('rated_player_id');

        $ranking = $players->map(function (Player $player) use ($gamePositions, $ratingsByPlayer, $snapshotsByPlayer): array {
            $playerSnapshots = $snapshotsByPlayer->get($player->id, collect())
                ->sortBy(fn (GamePlayerRating $snapshot): int => $gamePositions->get($snapshot->game_id, PHP_INT_MAX));
            $receivedRatings = $ratingsByPlayer->get($player->id, collect());
            $startRating = $playerSnapshots->first()?->rating;

            return [
                'id' => $player->id,
                'name' => $player->name,
                'rating' => round($player->rating / 100, 2),
                'start_rating' => $startRating === null ? null : round($startRating / 100, 2),
                'difference' => $startRating === null ? null : round(($player->rating - $startRating) / 100, 2),
                'games_played' => $playerSnapshots->count(),
                'average_rating' => $receivedRatings->isEmpty() ? null : round((float) $receivedRatings->avg('rating_value'), 1),
                'ratings_count' => $receivedRatings->count(),
            ];
        })->values()->map(function (array $player, int $index): array {
            $player['rank'] = $index + 1;

            return $player;
        })->values();

        $trendPlayers = $selectedPlayerIds->isEmpty()
            ? $ranking->filter(fn (array $player): bool => $player['games_played'] > 0)
                ->sortByDesc('games_played')
                ->take(15)
            : $ranking->whereIn('id', $selectedPlayerIds);
        $trendPlayers = $trendPlayers->values();

        $ratingTrend = [
            'labels' => $selectedGames->map(fn (Game $game): string => $this->dateValue($game->played_at))->all(),
            'datasets' => $trendPlayers->map(function (array $player, int $index) use ($snapshotsByGame, $selectedGames): array {
                $color = self::PLAYER_CHART_COLORS[$index % count(self::PLAYER_CHART_COLORS)]['background'];

                return [
                    'label' => $player['name'],
                    'data' => $selectedGames->map(function (Game $game) use ($snapshotsByGame, $player): ?float {
                        $snapshot = $snapshotsByGame->get($game->id, collect())->firstWhere('player_id', $player['id']);

                        return $snapshot === null ? null : round($snapshot->rating / 100, 2);
                    })->values()->all(),
                    'borderColor' => $color,
                    'backgroundColor' => $color,
                    'pointBorderColor' => $color,
                    'fill' => false,
                ];
            })->values()->all(),
        ];

        $historicalPlayerIds = $snapshots->pluck('player_id')->merge($ratings->pluck('rated_player_id'))->unique()->values();
        $historicalPlayers = Player::withTrashed()->whereIn('id', $historicalPlayerIds)->get(['id', 'name']);
        $averageRatingPlayers = $historicalPlayers->map(function (Player $player) use ($ratingsByPlayer, $snapshotsByPlayer): array {
            $receivedRatings = $ratingsByPlayer->get($player->id, collect());

            return [
                'id' => $player->id,
                'name' => $player->name,
                'average_rating' => $receivedRatings->isEmpty() ? null : round((float) $receivedRatings->avg('rating_value'), 1),
                'ratings_count' => $receivedRatings->count(),
                'games_played' => $snapshotsByPlayer->get($player->id, collect())->count(),
            ];
        })->filter(fn (array $player): bool => $player['average_rating'] !== null);
        if ($selectedPlayerIds->isNotEmpty()) {
            $averageRatingPlayers = $averageRatingPlayers->whereIn('id', $selectedPlayerIds);
        } else {
            $averageRatingPlayers = $averageRatingPlayers
                ->sortByDesc('games_played')
                ->take(20);
        }
        $averageRatingPlayers = $averageRatingPlayers
            ->sortByDesc('average_rating')
            ->values();
        $averageRatings = [
            'labels' => $averageRatingPlayers->map(fn (array $player): string => $player['name'])->all(),
            'datasets' => [[
                'label' => 'Average rating',
                'data' => $averageRatingPlayers->pluck('average_rating')->all(),
                'valueCounts' => $averageRatingPlayers->pluck('ratings_count')->all(),
                'backgroundColor' => '#3678b8',
                'borderColor' => '#245d91',
            ]],
        ];

        $selectedPlayers = $players->whereIn('id', $selectedPlayerIds)->values();
        $playerResults = [
            'player_names' => $selectedPlayers->pluck('name')->all(),
            'labels' => ['Wins', 'Draws', 'Losses'],
            'datasets' => [],
        ];
        foreach ($selectedPlayers as $index => $selectedPlayer) {
            $resultCounts = ['win' => 0, 'draw' => 0, 'loss' => 0];
            foreach ($selectedGames as $game) {
                $gamePlayer = $game->teams->firstWhere('id', $selectedPlayer->id);
                if ($gamePlayer === null) {
                    continue;
                }

                $playerTeam = $this->teamKey($gamePlayer->pivot->team);
                $result = $this->outcome($game);
                $resultKey = 'loss';
                if ($result === 'draw') {
                    $resultKey = 'draw';
                } elseif ($result === $playerTeam) {
                    $resultKey = 'win';
                }
                $resultCounts[$resultKey]++;
            }
            $color = self::PLAYER_CHART_COLORS[$index % count(self::PLAYER_CHART_COLORS)];
            $playerResults['datasets'][] = [
                'label' => $selectedPlayer->name,
                'data' => array_values($resultCounts),
                'backgroundColor' => $color['background'],
                'borderColor' => $color['border'],
            ];
        }

        $requestCounts = ['completed' => 0, 'pending' => 0, 'expired' => 0, 'send_failed' => 0];
        foreach ($selectedGames->flatMap->ratingRequests as $ratingRequest) {
            $status = $ratingRequest->displayStatus();
            if (array_key_exists($status, $requestCounts)) {
                $requestCounts[$status]++;
            }
        }

        $summaryRatings = $selectedPlayerIds->isEmpty()
            ? $ratings
            : $ratings->whereIn('rated_player_id', $selectedPlayerIds);
        $summaryRequests = $selectedGames->flatMap->ratingRequests;
        if ($selectedPlayerIds->isNotEmpty()) {
            $summaryRequests = $summaryRequests->whereIn('player_id', $selectedPlayerIds);
        }
        $summaryRequestCounts = ['completed' => 0, 'pending' => 0, 'expired' => 0, 'send_failed' => 0];
        foreach ($summaryRequests as $ratingRequest) {
            $status = $ratingRequest->displayStatus();
            if (array_key_exists($status, $summaryRequestCounts)) {
                $summaryRequestCounts[$status]++;
            }
        }
        $selectedGamesPlayed = $selectedPlayerIds->map(fn (int $id): int => $snapshotsByPlayer->get($id, collect())->count());
        $summaryGamesPlayed = $selectedPlayerIds->isEmpty()
            ? $selectedGames->count()
            : ($selectedPlayerIds->count() > 1
                ? round((float) $selectedGamesPlayed->avg(), 1)
                : $selectedGamesPlayed->first());
        $headlineRating = $selectedPlayerIds->count() === 1
            ? round($players->firstWhere('id', $selectedPlayerIds->first())->rating / 100, 2)
            : ($selectedPlayerIds->isEmpty()
            ? ($players->isEmpty() ? null : round((float) $players->avg('rating') / 100, 2))
            : round((float) $players->whereIn('id', $selectedPlayerIds)->avg('rating') / 100, 2));

        $ratingRequests = [
            'labels' => ['Completed', 'Pending', 'Expired', 'Send failed'],
            'datasets' => [[
                'label' => 'Rating requests',
                'data' => array_values($requestCounts),
                'backgroundColor' => ['#2e9b73', '#3678b8', '#e0a11b', '#d64b4b'],
                'borderColor' => '#ffffff',
                'borderWidth' => 3,
            ]],
            'counts' => $requestCounts,
        ];

        $recentGames = $selectedGames->reverse()->take(5)->values()->map(function (Game $game): array {
            return [
                'id' => $game->id,
                'played_at' => $this->dateValue($game->played_at),
                'team1_score' => $game->team1_score,
                'team2_score' => $game->team2_score,
                'players_count' => $game->teams->count(),
                'request_counts' => $this->ratingRequestCounts($game),
            ];
        })->all();

        return Inertia::render('Dashboard', [
            'filters' => ['period' => $period, 'player_ids' => $selectedPlayerIds->all()],
            'players' => $players->sortBy('name')->values()->map(fn (Player $player): array => [
                'id' => $player->id,
                'name' => $player->name,
            ])->values()->all(),
            'summary' => [
                'active_players' => $players->count(),
                'headline_rating' => $headlineRating,
                'headline_rating_label' => $selectedPlayerIds->count() === 1 ? 'Current rating' : ($selectedPlayerIds->isEmpty() ? 'Average current rating' : 'Average selected rating'),
                'games_played_label' => $selectedPlayerIds->count() > 1 ? 'Average games played' : 'Games played',
                'games_played' => $summaryGamesPlayed,
                'average_received_rating' => $summaryRatings->isEmpty() ? null : round((float) $summaryRatings->avg('rating_value'), 1),
                'open_rating_requests' => $summaryRequestCounts['pending'] + $summaryRequestCounts['send_failed'],
                'selected_player_name' => $selectedPlayers->count() === 1 ? $selectedPlayers->first()->name : null,
                'selected_player_count' => $selectedPlayerIds->count(),
            ],
            'ranking' => $ranking->all(),
            'charts' => [
                'ratingTrend' => $ratingTrend,
                'averageRatings' => $averageRatings,
                'playerResults' => $playerResults,
                'ratingRequests' => $ratingRequests,
            ],
            'recentGames' => $recentGames,
        ]);
    }

    private function dateValue($value): string
    {
        return $value instanceof \DateTimeInterface ? $value->format('c') : (string) $value;
    }

    private function teamKey($team): string
    {
        return in_array((string) $team, ['team1', '1'], true) ? 'team1' : 'team2';
    }

    private function outcome(Game $game): string
    {
        if ((int) $game->team1_score === (int) $game->team2_score) {
            return 'draw';
        }

        return (int) $game->team1_score > (int) $game->team2_score ? 'team1' : 'team2';
    }

    private function ratingRequestCounts(Game $game): array
    {
        $counts = ['completed' => 0, 'pending' => 0, 'expired' => 0, 'send_failed' => 0, 'revoked' => 0];
        foreach ($game->ratingRequests as $ratingRequest) {
            $status = $ratingRequest->displayStatus();
            if (array_key_exists($status, $counts)) {
                $counts[$status]++;
            }
        }

        return $counts;
    }
}
