<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PlayerController extends Controller
{
    public function index()
    {
        $players = Player::with('user')->get();
        
        return Inertia::render('Players/Index', [
            'players' => $players
        ]);
    }

    public function create()
    {
        return Inertia::render('Players/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:attacker,defender,both',
        ]);
        
        $validated['user_id'] = Auth::id();
        
        Player::create($validated);
        
        return redirect()->route('players.index')
            ->with('message', 'Player created successfully.');
    }

    public function edit(Player $player)
    {
        return Inertia::render('Players/Edit', [
            'player' => $player
        ]);
    }

    public function update(Request $request, Player $player)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:attacker,defender,both',
        ]);
        
        $player->update($validated);
        
        return redirect()->route('players.index')
            ->with('message', 'Player updated successfully.');
    }

    public function destroy(Player $player)
    {
        $player->delete();
        
        return redirect()->route('players.index')
            ->with('message', 'Player deleted successfully.');
    }
}