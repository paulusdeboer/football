<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GamePlayerRating;
use App\Models\Player;
use App\Models\RatingRequest;
use App\Services\RatingCalculator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Mail\RatingRequestMail;
use Illuminate\View\View;

class GameController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        $sortBy = $request->get('sort_by', 'played_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        $allowedSortColumns = ['played_at', 'team1_score', 'team2_score'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'played_at';
        }

        $games = Game::orderBy($sortBy, $sortDirection)->get();

        return view('games.index', compact('games', 'user', 'sortBy', 'sortDirection'));
    }

    public function create(): View
    {
        $players = Player::orderBy('name')->get();
        $user = auth()->user();

        return view('games.create', compact('players', 'user'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->validateGameRequest($request);

        $game = Game::create([
            'played_at' => $request->played_at,
        ]);

        $players = Player::whereIn('id', $request->players)->get();

        $this->storeGamePlayerRatings($game, $players);
        $this->handleGameTeams($game, $players);

        return redirect()->route('games.show', $game);
    }

    public function show(Game $game): View
    {
        $user = auth()->user();

        $ratingRequests = $game->ratingRequests()->get();

        $team1Ratings = $game->gamePlayerRatings()
            ->whereHas('player', function ($query) use ($game) {
                $query->whereHas('teams', function ($subQuery) use ($game) {
                    $subQuery->where('game_id', $game->id)
                        ->where('team', 'team1');
                });
            })
            ->with('player')
            ->get();

        $team2Ratings = $game->gamePlayerRatings()
            ->whereHas('player', function ($query) use ($game) {
                $query->whereHas('teams', function ($subQuery) use ($game) {
                    $subQuery->where('game_id', $game->id)
                        ->where('team', 'team2');
                });
            })
            ->with('player')
            ->get();

        $team1Rating = $team1Ratings->sum('rating');
        $team2Rating = $team2Ratings->sum('rating');

        return view('games.show', compact('game', 'user', 'ratingRequests', 'team1Rating', 'team2Rating', 'team1Ratings', 'team2Ratings'));
    }

    public function edit($id): View
    {
        $game = Game::findOrFail($id);
        $user = auth()->user();

        $players = Player::orderBy('name')->get();

        $selectedPlayers = $game->teams->pluck('id')->toArray();

        return view('games.edit', compact('game', 'user', 'players', 'selectedPlayers'));
    }

    public function update(Request $request, Game $game): RedirectResponse
    {
        $this->validateGameRequest($request);

        $game->update([
            'played_at' => $request->played_at,
        ]);

        $players = Player::whereIn('id', $request->players)->get();

        $game->teams()->detach();
        $game->gamePlayerRatings()->delete();

        $this->storeGamePlayerRatings($game, $players);
        $this->handleGameTeams($game, $players);

        return redirect()->route('games.show', $game)->with('success', __('Game updated successfully.'));
    }

    public function destroy($id): RedirectResponse
    {
        $game = Game::findOrFail($id);
        $game->delete();
        return redirect()->route('games.index')->with('success', __('Game deleted successfully.'));
    }

    public function enterResult(Game $game): View
    {
        $user = auth()->user();
        return view('games.enter-result', compact('game', 'user'));
    }

    public function storeResult(Request $request, Game $game): RedirectResponse
    {
        $game->update([
            'team1_score' => $request->team1_score,
            'team2_score' => $request->team2_score,
        ]);

        $this->updatePlayerRatings($game);

        if ($request->send_rating_requests && !$game->ratingRequests()->exists()) {
            $this->sendRatingRequests($game);
        }

        return redirect()->route('games.show', $game);
    }

    private function validateGameRequest(Request $request): void
    {
        $request->validate([
            'players' => 'required|array|size:12',
            'players.*' => 'exists:players,id',
            'played_at' => 'required|date',
        ], [
            'players.required' => __('You must select 12 players for the game'),
            'players.size' => __('Exactly 12 players are required'),
            'players.*.exists' => __('Some of the selected players are invalid'),
            'played_at.required' => __('The game date is required'),
            'played_at.date' => __('The game date must be a valid date'),
        ]);
    }

    private function handleGameTeams(Game $game, $players): void
    {
        $teams = $this->createTeams($players);

        $syncData = [];
        foreach ($teams as $team => $players) {
            foreach ($players as $player) {
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

    private function createTeams($players): array
    {
        $attackers = $players->where('type', 'attacker')->sortByDesc('rating')->values();
        $defenders = $players->where('type', 'defender')->sortByDesc('rating')->values();
        $allRounders = $players->where('type', 'both')->sortByDesc('rating')->values();

        $team1 = collect();
        $team2 = collect();

        $this->balanceAttackersAndDefenders($attackers, $defenders, $allRounders);

        $this->distributeAttackersAndDefenders($team1, $team2, $attackers, $defenders);

        $this->distributeDraftStyle($team1, $team2, $allRounders);

        $this->optimizeTeams($team1, $team2);

        return ['team1' => $team1, 'team2' => $team2];
    }

    private function balanceAttackersAndDefenders(&$attackers, &$defenders, $allRounders): void
    {
        if ($attackers->count() % 2 !== 0 && $allRounders->isNotEmpty()) {
            // Add the last all-rounder to attackers and remove it from allRounders
            $attackers->push($allRounders->pop());
            $attackers = $attackers->sortByDesc('rating')->values();
        }

        if ($defenders->count() % 2 !== 0 && $allRounders->isNotEmpty()) {
            // Add the last all-rounder to defenders and remove it from allRounders
            $defenders->push($allRounders->pop());
            $defenders = $defenders->sortByDesc('rating')->values();
        }
    }

    private function distributeAttackersAndDefenders($team1, $team2, &$attackers, &$defenders): void
    {
        $attackers = $this->checkAndDistributePlayers($team1, $team2, $attackers);

        $defenders = $this->checkAndDistributePlayers($team1, $team2, $defenders);

        $remainingPlayers = $attackers->merge($defenders);
        if ($remainingPlayers->isNotEmpty()) {
            $this->distributeDraftStyle($team1, $team2, $remainingPlayers);
        }
    }

    private function checkAndDistributePlayers($team1, $team2, $players): Collection
    {
        if ($players->count() >= 2) {
            if ($players->count() == 2) {
                $this->distributeDraftStyle($team1, $team2, $players);
            } else {
                // If there are more than 2 players, distribute the best 2 players
                $bestPlayers = $players->slice(0, 2);
                $this->distributeDraftStyle($team1, $team2, $bestPlayers);
            }
            // Remove the distributed players from the group
            return $players->slice(2);
        }
        return $players;
    }

    private function distributeDraftStyle($team1, $team2, $players): void
    {
        foreach ($players as $player) {
            // Distribute players based on the number of players in each team
            if ($team1->count() < $team2->count() || $team1->sum('rating') < $team2->sum('rating')) {
                $team1->push($player);
            } else {
                $team2->push($player);
            }
        }
    }

    private function optimizeTeams(&$team1, &$team2): void
    {
        $swappedWith = [];
        $madeSwap = true;
        $tolerance = 20;

        while ($madeSwap) {
            $madeSwap = false;
            $bestSwap = null;
            $team1Rating = $team1->sum('rating');
            $team2Rating = $team2->sum('rating');

            // Stop if the difference in team ratings is within the allowed tolerance
            if (abs($team1Rating - $team2Rating) <= $tolerance) {
                break;
            }

            foreach ($team1 as $player1) {
                foreach ($team2 as $player2) {
                    // Ensure that the types are the same
                    if ($player1->type !== $player2->type) {
                        continue;
                    }

                    $newTeam1Rating = $team1Rating - $player1->rating + $player2->rating;
                    $newTeam2Rating = $team2Rating - $player2->rating + $player1->rating;

                    // Check if the swap improves the team balance
                    if (abs($newTeam1Rating - $newTeam2Rating) < abs($team1Rating - $team2Rating)) {
                        if (!in_array($player2->id, $swappedWith)) {
                            // Save the best swap for this iteration
                            if (!$bestSwap || abs($newTeam1Rating - $newTeam2Rating) < abs($bestSwap['ratingDiff'])) {
                                $bestSwap = [
                                    'player1' => $player1,
                                    'player2' => $player2,
                                    'ratingDiff' => $newTeam1Rating - $newTeam2Rating
                                ];
                            }
                        }
                    }
                }
            }

            if ($bestSwap) {
                // Use filter to remove the players from their current teams
                $team1 = $team1->filter(fn($player) => $player->id !== $bestSwap['player1']->id);
                $team2 = $team2->filter(fn($player) => $player->id !== $bestSwap['player2']->id);

                // Swap players between the teams
                $team1->push($bestSwap['player2']);
                $team2->push($bestSwap['player1']);

                $swappedWith[] = $bestSwap['player2']->id;
                $madeSwap = true;
            }
        }
    }

    private function updatePlayerRatings(Game $game): void
    {
        $ratingCalculator = new RatingCalculator();
        $newRatings = $ratingCalculator->calculate($game);

        foreach ($newRatings as $playerId => $newRating) {
            Player::where('id', $playerId)->update(['rating' => $newRating]);
        }
    }

    private function sendRatingRequests(Game $game): void
    {
        // Select 3 random players and send them email requests to rate others
        $players = $game->teams()->inRandomOrder()->limit(3)->get();

        foreach ($players as $player) {
            $tokenUrl = URL::temporarySignedRoute(
                'players.rate', now()->addHours(72), ['game' => $game->id, 'player' => $player->id]
            );

            try {
                RatingRequest::create([
                    'game_id' => $game->id,
                    'player_id' => $player->id
                ]);

                Mail::to($player->user->email)->send(new RatingRequestMail($game, $tokenUrl));

            } catch (\Exception $e) {
                Log::error("Failed to send rating request to player ID $player->id for game ID $game->id: " . $e->getMessage());
            }
        }
    }
}
