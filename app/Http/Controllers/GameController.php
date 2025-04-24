<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GameController extends Controller
{
    public function index()
    {
        $games = Game::latest('played_at')->get();
        
        return Inertia::render('Games/Index', [
            'games' => $games
        ]);
    }

    public function create()
    {
        $players = Player::all();
        
        return Inertia::render('Games/Create', [
            'players' => $players
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'played_at' => 'required|date',
            'team1_players' => 'required|array|min:1',
            'team1_players.*' => 'exists:players,id',
            'team2_players' => 'required|array|min:1',
            'team2_players.*' => 'exists:players,id',
            'team1_score' => 'nullable|integer|min:0',
            'team2_score' => 'nullable|integer|min:0',
        ]);
        
        $game = Game::create([
            'played_at' => $validated['played_at'],
            'team1_score' => $validated['team1_score'] ?? null,
            'team2_score' => $validated['team2_score'] ?? null,
        ]);
        
        // Attach players to teams
        foreach ($validated['team1_players'] as $playerId) {
            $game->teams()->attach($playerId, ['team' => 'team1']);
        }
        
        foreach ($validated['team2_players'] as $playerId) {
            $game->teams()->attach($playerId, ['team' => 'team2']);
        }
        
        return redirect()->route('games.index')
            ->with('message', 'Game created successfully.');
    }

    public function show(Game $game)
    {
        $game->load(['teams']);
        
        $team1Players = $game->teams()->wherePivot('team', 'team1')->get();
        $team2Players = $game->teams()->wherePivot('team', 'team2')->get();
        
        return Inertia::render('Games/Show', [
            'game' => $game,
            'team1Players' => $team1Players,
            'team2Players' => $team2Players
        ]);
    }

    public function edit(Game $game)
    {
        $game->load(['teams']);
        
        $team1Players = $game->teams()->wherePivot('team', 'team1')->pluck('players.id');
        $team2Players = $game->teams()->wherePivot('team', 'team2')->pluck('players.id');
        $players = Player::all();
        
        return Inertia::render('Games/Edit', [
            'game' => $game,
            'team1Players' => $team1Players,
            'team2Players' => $team2Players,
            'players' => $players
        ]);
    }

    public function update(Request $request, Game $game)
    {
        $validated = $request->validate([
            'played_at' => 'required|date',
            'team1_players' => 'required|array|min:1',
            'team1_players.*' => 'exists:players,id',
            'team2_players' => 'required|array|min:1', 
            'team2_players.*' => 'exists:players,id',
            'team1_score' => 'nullable|integer|min:0',
            'team2_score' => 'nullable|integer|min:0',
        ]);
        
        $game->update([
            'played_at' => $validated['played_at'],
            'team1_score' => $validated['team1_score'] ?? null,
            'team2_score' => $validated['team2_score'] ?? null,
        ]);
        
        // Sync teams
        $game->teams()->detach();
        
        foreach ($validated['team1_players'] as $playerId) {
            $game->teams()->attach($playerId, ['team' => 'team1']);
        }
        
        foreach ($validated['team2_players'] as $playerId) {
            $game->teams()->attach($playerId, ['team' => 'team2']);
        }
        
        return redirect()->route('games.index')
            ->with('message', 'Game updated successfully.');
    }

    public function destroy(Game $game)
    {
        $game->delete();
        
        return redirect()->route('games.index')
            ->with('message', 'Game deleted successfully.');
    }
}