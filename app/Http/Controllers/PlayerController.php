<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\User;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    public function index(Request $request)
    {
        $query = Player::query();
        if ($request->has('include_deleted') && $request->input('include_deleted') == '1') {
            $query->withTrashed();
        }

        $players = $query->orderBy('name')->get();
        $user = auth()->user();

        return view('players.index', compact('players', 'user'));
    }

    public function create()
    {
        $user = auth()->user();

        return view('players.create', compact('user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'players' => 'required|array',
            'players.*.name' => 'required|string|max:255',
            'players.*.email' => 'required|email',
            'players.*.rating' => 'required|numeric|min:0|max:10',
            'players.*.type' => 'required|in:attacker,defender,both',
        ]);

        $players = $request->players;

        foreach ($players as $player) {
            $user = User::create([
                'name' => $player['name'],
                'email' => $player['email'],
                'password' => bcrypt('default_password'),
            ]);

            Player::create([
                'name' => $player['name'],
                'rating' => $player['rating'] * 100,
                'type' => $player['type'],
                'user_id' => $user->id,
            ]);
        }

        return redirect()->route('players.index')->with('success', __('Player created successfully.'));
    }

    public function edit(string $id)
    {
        $player = Player::findOrFail($id);
        $user = auth()->user();

        return view('players.edit', compact('player', 'user'));
    }

    public function update(Request $request, Player $player)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'rating' => 'required|numeric|min:0|max:10',
            'type' => 'required|in:attacker,defender,both',
        ]);

        $player->update([
            'name' => $request->name,
            'email' => $request->email,
            'rating' => $request->rating * 100,
            'type' => $request->type,
        ]);

        return redirect()->route('players.index')->with('success', __('Player updated successfully.'));
    }

    public function destroy(string $id)
    {
        $player = Player::findOrFail($id);
        $player->delete();

        return redirect()->route('players.index')->with('success', __('Player deleted successfully.'));
    }

    public function restore($id)
    {
        $player = Player::withTrashed()->findOrFail($id);

        $player->restore();

        return redirect()->route('players.index')->with('status', __('Player restored successfully.'));
    }
}
