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
        // Validation to ensure exactly 12 players are selected
        $request->validate([
            'players' => 'required|array|size:12',
            'players.*' => 'exists:players,id',
        ]);

        // Retrieve the selected players
        $selectedPlayers = Player::whereIn('id', $request->players)->get();

        // Divide players into two teams
        $team1 = $selectedPlayers->slice(0, 6);
        $team2 = $selectedPlayers->slice(6, 6);

        // Create a new game with the selected date
        $game = Game::create(['played_at' => $request->played_at]);

        // Assign players to teams
        foreach ($team1 as $player) {
            $game->teams()->attach($player->id, ['team' => 'team1']);
        }
        foreach ($team2 as $player) {
            $game->teams()->attach($player->id, ['team' => 'team2']);
        }

        return redirect()->route('games.show', $game);
    }

    // Show the specified game.
    public function show(Game $game)
    {
        $user = auth()->user();
        return view('games.show', compact('game', 'user'));
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
        // Validate the input
        $request->validate([
            'played_at' => 'required|date',
            'players' => 'required|array|min:12|max:12',
            'players.*' => 'exists:players,id',
        ]);

        // Update the game date
        $game->update([
            'played_at' => $request->played_at,
        ]);

        // Reassign teams based on selected players
        $selectedPlayers = Player::whereIn('id', $request->players)->get();

        // Split players into two teams
        $team1 = $selectedPlayers->slice(0, 6);
        $team2 = $selectedPlayers->slice(6, 6);

        // Sync the teams with the new players
        $game->teams()->detach(); // Remove existing team assignments
        foreach ($team1 as $player) {
            $game->teams()->attach($player->id, ['team' => 'team1']);
        }
        foreach ($team2 as $player) {
            $game->teams()->attach($player->id, ['team' => 'team2']);
        }

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
