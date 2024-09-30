<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Mail\RatingRequestMail;

class GameController extends Controller
{
    // Display a listing of the games.
    public function index()
    {
        $games = Game::orderBy('played_at', 'desc')->get();
        $user = auth()->user();

        return view('games.index', compact('games', 'user'));
    }

    // Show the form for creating a new game.
    public function create()
    {
        $players = Player::all();
        $user = auth()->user();

        return view('games.create', compact('players', 'user'));
    }

    // Store a newly created game in storage.
    public function store(Request $request)
    {
        $this->validateGameRequest($request);

        // Create a new game
        $game = Game::create([
            'played_at' => $request->played_at,
        ]);

        $this->handleGameTeams($game, Player::whereIn('id', $request->players)->get());

        return redirect()->route('games.show', $game);
    }

    // Show the specified game.
    public function show(Game $game)
    {
        $user = auth()->user();

        // Calculate the total ratings for each team
        $team1Rating = $game->teams()->where('team', 'team1')->sum('rating');
        $team2Rating = $game->teams()->where('team', 'team2')->sum('rating');

        return view('games.show', compact('game', 'user', 'team1Rating', 'team2Rating'));
    }

    // Show the form for editing the specified game.
    public function edit($id)
    {
        $game = Game::findOrFail($id);
        $user = auth()->user();

        // Get all players to display in the form
        $players = Player::all(); // Retrieve all players

        // Get the currently selected players for the game
        $selectedPlayers = $game->teams->pluck('id')->toArray();

        return view('games.edit', compact('game', 'user', 'players', 'selectedPlayers'));
    }

    // Update the specified game in storage.
    public function update(Request $request, Game $game)
    {
        $this->validateGameRequest($request);

        // Update the game date
        $game->update([
            'played_at' => $request->played_at,
        ]);

        $this->handleGameTeams($game, Player::whereIn('id', $request->players)->get());

        return redirect()->route('games.show', $game);
    }

    // Remove the specified game from storage.
    public function destroy($id)
    {
        $game = Game::findOrFail($id);
        $game->delete();
        return redirect()->route('games.index');
    }

    // Show form for entering result of the game
    public function enterResult(Game $game)
    {
        $user = auth()->user();
        return view('games.enter-result', compact('game', 'user'));
    }

    // Store the entered result of the game
    public function storeResult(Request $request, Game $game)
    {
        // Update the game with the result
        $game->update([
            'team1_score' => $request->team1_score,
            'team2_score' => $request->team2_score,
        ]);

        // Adjust player ratings based on the result
        $this->adjustRatings($game);

        // Send rating requests to 3 random players
        $this->sendRatingRequests($game);

        return redirect()->route('games.show', $game);
    }

    private function validateGameRequest(Request $request)
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

    private function handleGameTeams(Game $game, $players)
    {
        $teams = $this->createTeams($players);

        // Prepare the data for sync
        $syncData = [];
        foreach ($teams as $team => $players) {
            foreach ($players as $player) {
                $syncData[$player->id] = ['team' => $team];
            }
        }

        // Sync the teams with the new players
        $game->teams()->sync($syncData);
    }

    private function createTeams($players)
    {
        // Split players by type
        $attackers = $players->where('type', 'attacker')->sortByDesc('rating')->values();
        $defenders = $players->where('type', 'defender')->sortByDesc('rating')->values();
        $allRounders = $players->where('type', 'both')->sortByDesc('rating')->values();

        // Create empty teams
        $team1 = collect();
        $team2 = collect();

        // Balance attackers and defenders with all-rounders
        $this->balanceAttackersAndDefenders($attackers, $defenders, $allRounders);

        // Then distribute attackers and defenders
        $this->distributeAttackersAndDefenders($team1, $team2, $attackers, $defenders);

        // Distribute remaining all-rounders
        $this->distributeDraftStyle($team1, $team2, $allRounders);

        $this->optimizeTeams($team1, $team2);

        return ['team1' => $team1, 'team2' => $team2];
    }

    private function balanceAttackersAndDefenders(&$attackers, &$defenders, $allRounders)
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

    private function distributeAttackersAndDefenders($team1, $team2, &$attackers, &$defenders)
    {
        // Check and distribute attackers
        $attackers = $this->checkAndDistributePlayers($team1, $team2, $attackers);

        // Check and distribute defenders
        $defenders = $this->checkAndDistributePlayers($team1, $team2, $defenders);

        // Merge remaining players (if any) and distribute
        $remainingPlayers = $attackers->merge($defenders);
        if ($remainingPlayers->isNotEmpty()) {
            $this->distributeDraftStyle($team1, $team2, $remainingPlayers);
        }
    }

    private function checkAndDistributePlayers($team1, $team2, $players)
    {
        if ($players->count() >= 2) {
            if ($players->count() == 2) {
                // If there are exactly 2 players in the group, distribute them directly
                $this->distributeDraftStyle($team1, $team2, $players);
            } else {
                // If there are more than 2, distribute the best 2 players
                $bestPlayers = $players->slice(0, 2);
                $this->distributeDraftStyle($team1, $team2, $bestPlayers);
            }
            // Remove the distributed players from the group
            return $players->slice(2);
        }
        return $players;
    }

    private function distributeDraftStyle($team1, $team2, $players)
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

    private function optimizeTeams(&$team1, &$team2)
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

            // Perform the best swap found in this iteration
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

    private function adjustRatings(Game $game)
    {
        // Logic to adjust player ratings based on game result
    }

    private function sendRatingRequests(Game $game)
    {
        // Select 3 random players and send them email requests to rate others
        $players = $game->teams()->inRandomOrder()->limit(3)->get();
        foreach ($players as $player) {
            $tokenUrl = URL::temporarySignedRoute(
                'players.rate', now()->addHours(24), ['game' => $game->id, 'player' => $player->id]
            );

            // Send the rating request email
            Mail::to($player->user->email)->send(new RatingRequestMail($game, $tokenUrl));
        }
    }
}
