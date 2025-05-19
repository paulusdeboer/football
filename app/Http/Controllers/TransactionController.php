<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Setting;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\JsonResponse;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        $sortBy = $request->get('sort_by', 'date');
        $sortDirection = $request->get('sort_direction', 'desc');

        $allowedSortColumns = ['date', 'player_id', 'amount'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'date';
        }

        $transactions = Transaction::orderBy($sortBy, $sortDirection)->get();

        return view('transactions.index', compact('transactions', 'user', 'sortBy', 'sortDirection'));
    }

    public function create()
    {
        $user = auth()->user();

        return view('transactions.create', compact('user'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'amount' => 'required|numeric',
            'player_id' => 'nullable|exists:players,id',
            'account_id' => 'required|exists:accounts,id',
        ]);

        $transaction = Transaction::create($validated);

        if ($transaction->player) {
            $this->updatePlayerBalance($transaction->player);
        }
        
        // Update account balance
        $account = $transaction->account;
        if ($account) {
            $account->balance = $account->transactions()->sum('amount');
            $account->save();
        }

        return response()->json([
            'id' => $transaction->id,
            'date' => $transaction->date,
            'amount' => $transaction->amount,
            'player_name' => $transaction->player?->name,
            'account_balance' => $account ? $account->balance : null,
        ]);
    }

    private function updatePlayerBalance(Player $player): void
    {
        // We update the raw balance, the conversion to games is done in the view
        $player->balance = Transaction::where('player_id', $player->id)->sum('amount');
        $player->save();
    }
    
    public function destroy(Transaction $transaction)
    {
        $player = $transaction->player;
        $account = $transaction->account;
        
        $transaction->delete();
        
        if ($player) {
            $this->updatePlayerBalance($player);
        }
        
        // Update account balance
        if ($account) {
            $account->balance = $account->transactions()->sum('amount');
            $account->save();
        }
        
        return response()->json([
            'success' => true,
            'account_balance' => $account ? $account->balance : null
        ]);
    }
}
