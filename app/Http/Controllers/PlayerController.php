<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PlayerController extends Controller
{
    public function index(Request $request): Response
    {
        $sortBy = $request->get('sort_by', session('sort_by', 'name'));
        $sortDirection = $request->get('sort_direction', session('sort_direction', 'asc'));
        $allowedSortColumns = ['name', 'rating', 'type', 'created_at', 'email'];
        $sortBy = in_array($sortBy, $allowedSortColumns, true) ? $sortBy : 'name';
        $sortDirection = in_array($sortDirection, ['asc', 'desc'], true) ? $sortDirection : 'asc';
        $includeDeleted = $request->has('include_deleted')
            ? (string) $request->include_deleted
            : (string) session('include_deleted', '0');

        session([
            'sort_by' => $sortBy,
            'sort_direction' => $sortDirection,
            'include_deleted' => $includeDeleted,
        ]);

        $query = Player::query()->with('user');
        if ($includeDeleted === '1') {
            $query->withTrashed();
        }
        if ($sortBy === 'email') {
            $query->leftJoin('users', 'players.user_id', '=', 'users.id')
                ->orderBy('users.email', $sortDirection)
                ->select('players.*');
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        return Inertia::render('Players/Index', [
            'players' => $query->get(),
            'sortBy' => $sortBy,
            'sortDirection' => $sortDirection,
            'includeDeleted' => $includeDeleted,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Players/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'players' => ['required', 'array'],
            'players.*.name' => ['required', 'string', 'max:255'],
            'players.*.email' => ['required', 'email'],
            'players.*.rating' => ['required', 'numeric', 'min:0', 'max:10'],
            'players.*.type' => ['required', 'in:attacker,defender,both'],
        ]);

        DB::transaction(function () use ($request): void {
            foreach ($request->players as $data) {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => bcrypt('default_password'),
                ]);
                Player::create([
                    'name' => $data['name'],
                    'rating' => $data['rating'] * 100,
                    'type' => $data['type'],
                    'user_id' => $user->id,
                ]);
            }
        });

        $playerCount = count($request->input('players', []));

        return redirect()->route('players.index')->with(
            'success',
            trans_choice('players_created', $playerCount, ['count' => $playerCount]),
        );
    }

    public function edit(Player $player): Response
    {
        return Inertia::render('Players/Edit', [
            'player' => [
                'id' => $player->id,
                'name' => $player->name,
                'email' => $player->user?->email,
                'rating' => $player->rating / 100,
                'type' => $player->type,
            ],
        ]);
    }

    public function update(Request $request, Player $player): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'rating' => ['required', 'numeric', 'min:0', 'max:10'],
            'type' => ['required', 'in:attacker,defender,both'],
        ]);

        $player->update([
            'name' => $request->name,
            'rating' => $request->rating * 100,
            'type' => $request->type,
        ]);
        $player->user?->update(['email' => $request->email]);

        return redirect()->route('players.index')->with('success', __('Player updated successfully.'));
    }

    public function destroy(Player $player): RedirectResponse
    {
        $player->delete();

        return redirect()->route('players.index')->with('success', __('Player deleted successfully.'));
    }

    public function restore(int $id): RedirectResponse
    {
        Player::withTrashed()->findOrFail($id)->restore();

        return redirect()->route('players.index')->with('status', __('Player restored successfully.'));
    }
}
