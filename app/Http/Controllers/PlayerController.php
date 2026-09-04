<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PlayerController extends Controller
{
    public function index(Request $request): Response
    {
        $sortBy = $request->get('sort_by', session('sort_by', 'name'));
        $sortDirection = $request->get('sort_direction', session('sort_direction', 'asc'));
        $allowedSortColumns = ['name', 'username', 'rating', 'type', 'created_at', 'email'];
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

        $query = Player::query()->with(['user' => fn ($query) => $query->withTrashed()]);
        if ($includeDeleted === '1') {
            $query->withTrashed();
        }
        if (in_array($sortBy, ['email', 'username'], true)) {
            $query->leftJoin('users', 'players.user_id', '=', 'users.id')
                ->orderBy('users.'.($sortBy === 'email' ? 'email' : 'name'), $sortDirection)
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
            'players.*.name' => [
                'required',
                'string',
                'max:255',
                'distinct',
                Rule::unique('users', 'name'),
            ],
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
                'username' => $player->user?->name ?? $player->name,
                'email' => $player->user?->email,
                'rating' => $player->rating / 100,
                'type' => $player->type,
                'role' => $player->user?->role ?? User::ROLE_PLAYER,
            ],
        ]);
    }

    public function update(Request $request, Player $player): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'name')->ignore($player->user_id),
            ],
            'email' => ['required', 'email'],
            'rating' => ['required', 'numeric', 'min:0', 'max:10'],
            'type' => ['required', 'in:attacker,defender,both'],
            'role' => ['required', 'in:'.User::ROLE_ADMIN.','.User::ROLE_PLAYER],
        ]);

        $this->ensureRoleChangeAllowed($request, $player, $request->string('role')->toString());

        DB::transaction(function () use ($request, $player): void {
            $player->update([
                'name' => $request->name,
                'rating' => $request->rating * 100,
                'type' => $request->type,
            ]);
            $player->user?->update([
                'name' => $request->username,
                'email' => $request->email,
                'role' => $request->role,
            ]);
        });

        return redirect()->route('players.index')->with('success', __('Player updated successfully.'));
    }

    public function destroy(Request $request, Player $player): RedirectResponse
    {
        if ($player->user_id === $request->user()->id) {
            return redirect()->route('players.index')->with('error', __('You cannot delete your own player account.'));
        }

        if ($player->user?->isProtectedAdmin()) {
            return redirect()->route('players.index')->with('error', __('This administrator account is protected.'));
        }

        if ($player->user?->isAdmin() && User::where('role', User::ROLE_ADMIN)->count() === 1) {
            return redirect()->route('players.index')->with('error', __('The last administrator cannot be deleted.'));
        }

        $player->delete();

        return redirect()->route('players.index')->with('success', __('Player deleted successfully.'));
    }

    public function restore(int $id): RedirectResponse
    {
        Player::withTrashed()->findOrFail($id)->restore();

        return redirect()->route('players.index')->with('status', __('Player restored successfully.'));
    }

    private function ensureRoleChangeAllowed(Request $request, Player $player, string $role): void
    {
        if ($role !== User::ROLE_PLAYER || ! $player->user) {
            return;
        }

        if ($player->user_id === $request->user()->id) {
            throw ValidationException::withMessages([
                'role' => __('You cannot remove your own administrator role.'),
            ]);
        }

        if ($player->user->isProtectedAdmin()) {
            throw ValidationException::withMessages([
                'role' => __('This administrator account is protected.'),
            ]);
        }

        if ($player->user->isAdmin() && User::where('role', User::ROLE_ADMIN)->count() === 1) {
            throw ValidationException::withMessages([
                'role' => __('The last administrator cannot be demoted.'),
            ]);
        }
    }
}
