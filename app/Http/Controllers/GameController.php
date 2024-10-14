<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Mail\RatingRequestMail;
use Illuminate\View\View;

class GameController extends Controller
{
    public function index(): View
    {
        $games = Game::orderBy('played_at', 'desc')->get();
        $user = auth()->user();

        return view('games.index', compact('games', 'user'));
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

        // Create a new game
        $game = Game::create([
            'played_at' => $request->played_at,
        ]);

        $this->handleGameTeams($game, Player::whereIn('id', $request->players)->get());

        return redirect()->route('games.show', $game);
    }

    public function show(Game $game): View
    {
        $user = auth()->user();

        // Calculate the total ratings for each team
        $team1Rating = $game->teams()->where('team', 'team1')->sum('rating');
        $team2Rating = $game->teams()->where('team', 'team2')->sum('rating');

        return view('games.show', compact('game', 'user', 'team1Rating', 'team2Rating'));
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

        $this->handleGameTeams($game, Player::whereIn('id', $request->players)->get());

        return redirect()->route('games.show', $game);
    }

    public function destroy($id): RedirectResponse
    {
        $game = Game::findOrFail($id);
        $game->delete();
        return redirect()->route('games.index');
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

        $this->basicRatingAdjustment($game);

        if ($request->send_rating_requests) {
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

    private function checkAndDistributePlayers($team1, $team2, $players)
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

    private function basicRatingAdjustment(Game $game)
    {
        $team1Players = $game->teams()->where('team', 'team1')->get();
        $team2Players = $game->teams()->where('team', 'team2')->get();

        $team1Score = $game->team1_score;
        $team2Score = $game->team2_score;

        // Determine the winning and losing team
        if ($team1Score > $team2Score) {
            $winningTeam = $team1Players;
            $losingTeam = $team2Players;
        } elseif ($team1Score < $team2Score) {
            $winningTeam = $team2Players;
            $losingTeam = $team1Players;
        } else {
            // No rating changes
            return;
        }

        $scoreDifference = abs($team1Score - $team2Score);
        $this->procesScore($winningTeam, $scoreDifference, true);
        $this->procesScore($losingTeam, $scoreDifference, false);
    }

    private function procesScore($team, int $scoreDifference, bool $won)
    {
        foreach ($team as $player) {
            $player->previous_rating = $player->rating;

            $coefficient = $won ? 1 + ($scoreDifference / 100) : 1 - ($scoreDifference / 100);
            $newRating = $player->rating * $coefficient;

            $player->rating = $newRating;
            $player->save();
        }
    }

    private function sendRatingRequests(Game $game)
    {
        // Select 3 random players and send them email requests to rate others
        $players = $game->teams()->inRandomOrder()->limit(3)->get();

        foreach ($players as $player) {
            $tokenUrl = URL::temporarySignedRoute(
                'players.rate', now()->addHours(72), ['game' => $game->id, 'player' => $player->id]
            );

            // Log the URL for testing purposes
            Log::info("Generated rating URL: $tokenUrl");

            try {
                Mail::to($player->user->email)->send(new RatingRequestMail($game, $tokenUrl));

                Log::info("Rating request sent to player ID $player->id for game ID $game->id");
            } catch (\Exception $e) {
                Log::error("Failed to send rating request to player ID $player->id for game ID $game->id: " . $e->getMessage());
            }
        }
    }
}
