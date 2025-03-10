<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::latest()->paginate(15);
        return view('transactions.index', compact('transactions'));
    }

    public function create()
    {
        return view('transactions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'player_id' => 'nullable|exists:players,id',
            'amount' => 'required|numeric',
            'date' => 'required|date',
        ]);

        Transaction::create($validated);
        return redirect()->route('transactions.index')->with('success', __('Transaction added successfully.'));
    }
}
