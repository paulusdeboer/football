<?php

namespace App\Observers;

use App\Models\Transaction;

class TransactionObserver
{
    public function created(Transaction $transaction)
    {
        $transaction->account->increment('balance', $transaction->amount);
    }

    public function deleted(Transaction $transaction)
    {
        $transaction->account->decrement('balance', $transaction->amount);
    }
}
