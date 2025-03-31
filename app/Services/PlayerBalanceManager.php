<?php

namespace App\Services;

use App\Models\Player;

class PlayerBalanceManager
{
    /**
     * Update the balance of a player based on transactions.
     */
    public function updateBalance(Player $player, int $amount): void
    {
        $balanceHolder = $player->balance_holder ?? $player;
        $balanceHolder->balance += $amount;
        $balanceHolder->save();
    }

    /**
     * Deduct one match from the player's balance after playing.
     */
    public function deductMatch(Player $player): void
    {
        $balanceHolder = $player->balance_holder ?? $player;

        if ($balanceHolder->balance > 0) {
            $balanceHolder->balance -= 1;
            $balanceHolder->save();
        }
    }

    /**
     * Set the player's balance manually.
     */
    public function setBalance(Player $player, int $newBalance): void
    {
        $balanceHolder = $player->balance_holder ?? $player;
        $balanceHolder->balance = $newBalance;
        $balanceHolder->save();
    }
}
