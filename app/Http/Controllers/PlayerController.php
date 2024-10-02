<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\User;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    public function index()
    {
        $players = Player::orderBy('name', 'desc')->get();
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'rating' => 'required|integer|min:0|max:10',
            'type' => 'required|in:attacker,defender,both',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt('default_password'),
        ]);

        Player::create([
            'name' => $request->name,
            'rating' => $request->rating,
            'type' => $request->type,
            'user_id' => $user->id,
        ]);

        return redirect()->route('players.index')->with('success', __('Player created successfully.'));
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
