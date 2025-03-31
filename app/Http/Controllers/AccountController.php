<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Player;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'desc');

        $allowedSortColumns = ['name', 'balance', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'name';
        }

        $accounts = Account::orderBy($sortBy, $sortDirection)->get();

        return view('accounts.index', compact('accounts', 'user', 'sortBy', 'sortDirection'));
    }

    public function create(): View
    {
        $user = auth()->user();

        return view('accounts.create', compact('user'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'balance' => 'required|numeric',
        ]);

        Account::create($validated);

        return redirect()->route('accounts.index')->with('success', __('Account added successfully.'));
    }

    public function edit(string $id): View
    {
        $account = Account::findOrFail($id);
        $transactions = $account->transactions;
        $players = Player::all();
        $user = auth()->user();

        return view('accounts.edit', compact('account', 'transactions', 'players', 'user'));
    }

    public function update(Request $request, Account $account): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // We don't update the balance because it's disabled
        $account->update([
            'name' => $request->name,
        ]);

        return redirect()->route('accounts.index')->with('success', __('Account updated successfully.'));
    }

    public function destroy(string $id): RedirectResponse
    {
        $account = Account::findOrFail($id);
        $account->delete();

        return redirect()->route('accounts.index')->with('success', __('Account deleted successfully.'));
    }

    public function restore(string $id): RedirectResponse
    {
        $account = Account::withTrashed()->findOrFail($id);
        $account->restore();

        return redirect()->route('accounts.index')->with('status', __('Account restored successfully.'));
    }
}
