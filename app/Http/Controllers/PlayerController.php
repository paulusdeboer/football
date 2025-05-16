<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlayerController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        $sortBy = $request->get('sort_by', session('sort_by', 'name'));
        $sortDirection = $request->get('sort_direction', session('sort_direction', 'asc'));

        $allowedSortColumns = ['name', 'rating', 'type', 'created_at', 'email'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'name';
        }

        $includeDeleted = $request->has('include_deleted')
            ? $request->include_deleted
            : session('include_deleted', '0');

        session([
            'sort_by' => $sortBy,
            'sort_direction' => $sortDirection,
            'include_deleted' => $includeDeleted
        ]);

        $playersQuery = Player::query()->with('user');

        if ($includeDeleted == '1') {
            $playersQuery->withTrashed();
        }

        if ($sortBy === 'email') {
            $playersQuery->leftJoin('users', 'players.user_id', '=', 'users.id')
                ->orderBy('users.email', $sortDirection)
                ->select('players.*');
        } else {
            $playersQuery->orderBy($sortBy, $sortDirection);
        }

        $players = $playersQuery->get();

        return view('players.index', compact('players', 'user', 'sortBy', 'sortDirection'));
    }

    public function create(): View
    {
        $user = auth()->user();

        return view('players.create', compact('user'));
    }

    public function store(Request $request): RedirectResponse
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

    public function edit(string $id): View
    {
        $player = Player::findOrFail($id);
        $user = auth()->user();

        return view('players.edit', compact('player', 'user'));
    }

    public function update(Request $request, Player $player): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'rating' => 'required|numeric|min:0|max:10',
            'type' => 'required|in:attacker,defender,both',
        ]);

        $player->update([
            'name' => $request->name,
            'rating' => $request->rating * 100,
            'type' => $request->type,
        ]);
        
        if ($player->user) {
            $player->user->update([
                'email' => $request->email
            ]);
        }

        return redirect()->route('players.index')->with('success', __('Player updated successfully.'));
    }

    public function destroy(string $id): RedirectResponse
    {
        $player = Player::findOrFail($id);
        $player->delete();

        return redirect()->route('players.index')->with('success', __('Player deleted successfully.'));
    }

    public function restore($id): RedirectResponse
    {
        $player = Player::withTrashed()->findOrFail($id);

        $player->restore();

        return redirect()->route('players.index')->with('status', __('Player restored successfully.'));
    }
}
