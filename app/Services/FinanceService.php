<?php

namespace App\Services;

use App\Models\FinancialAudit;
use App\Models\FinancialSetting;
use App\Models\Game;
use App\Models\Player;
use App\Models\PlayerBalanceTransaction;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class FinanceService
{
    public function defaultMatchFeeCents(): int
    {
        return (int) (FinancialSetting::query()->value('default_match_fee_cents') ?? 0);
    }

    public function parseAmountToCents(mixed $value, bool $allowNegative = false): int
    {
        $normalized = str_replace(',', '.', trim((string) $value));

        if ($normalized === '' || ! preg_match('/^-?\d+(?:\.\d{1,2})?$/D', $normalized)) {
            throw new InvalidArgumentException('The amount must contain a number with at most two decimals.');
        }

        $negative = str_starts_with($normalized, '-');
        if ($negative && ! $allowNegative) {
            throw new InvalidArgumentException('The amount cannot be negative.');
        }

        $unsigned = ltrim($normalized, '-');
        [$whole, $fraction] = array_pad(explode('.', $unsigned, 2), 2, '');
        $cents = ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');

        return $negative ? -$cents : $cents;
    }

    public function balanceForPlayer(int $playerId): int
    {
        return (int) (PlayerBalanceTransaction::query()
            ->where('player_id', $playerId)
            ->sum('amount_cents'));
    }

    public function syncGameCharges(
        Game $game,
        Collection $participants,
        array $chargeAccounts,
        array $chargeUnits,
        int $feeCents,
        ?User $actor = null,
    ): void {
        $existing = $game->balanceTransactions()
            ->where('type', PlayerBalanceTransaction::TYPE_MATCH_CHARGE)
            ->get()
            ->keyBy('source_player_id');
        $seen = [];
        $occurredOn = $this->gameDate($game);

        foreach ($participants as $participant) {
            $participantId = (int) $participant->id;
            $accountId = (int) $this->mappedValue($chargeAccounts, $participantId, $participantId);
            $units = (int) $this->mappedValue($chargeUnits, $participantId, 1);

            if ($accountId < 1 || ! Player::withTrashed()->whereKey($accountId)->exists()) {
                throw ValidationException::withMessages([
                    'charge_accounts' => __('A selected financial account is invalid.'),
                ]);
            }

            if ($units < 0) {
                throw ValidationException::withMessages([
                    'charge_units' => __('The number of game units cannot be negative.'),
                ]);
            }

            $attributes = [
                'player_id' => $accountId,
                'source_player_id' => $participantId,
                'game_id' => $game->id,
                'type' => PlayerBalanceTransaction::TYPE_MATCH_CHARGE,
                'amount_cents' => -($units * $feeCents),
                'occurred_on' => $occurredOn,
                'units' => $units,
                'unit_price_cents' => $feeCents,
                'updated_by' => $actor?->id,
            ];

            $seen[$participantId] = true;
            $transaction = $existing->get($participantId);

            if ($transaction instanceof PlayerBalanceTransaction) {
                $this->updateTransactionRecord($transaction, $attributes, $actor);
            } else {
                $this->createTransactionRecord([
                    ...$attributes,
                    'created_by' => $actor?->id,
                ], $actor);
            }
        }

        foreach ($existing as $participantId => $transaction) {
            if (! isset($seen[(int) $participantId])) {
                $this->deleteTransactionRecord($transaction, $actor);
            }
        }
    }

    public function createManualTransaction(
        int $playerId,
        string $type,
        int $amountCents,
        string $occurredOn,
        User $actor,
    ): PlayerBalanceTransaction {
        if (! in_array($type, [
            PlayerBalanceTransaction::TYPE_OPENING_BALANCE,
            PlayerBalanceTransaction::TYPE_TOP_UP,
        ], true)) {
            throw new InvalidArgumentException('The transaction type is not editable here.');
        }

        return $this->createTransactionRecord([
            'player_id' => $playerId,
            'type' => $type,
            'amount_cents' => $amountCents,
            'occurred_on' => $occurredOn,
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ], $actor);
    }

    public function updateManualTransaction(
        PlayerBalanceTransaction $transaction,
        int $amountCents,
        string $occurredOn,
        User $actor,
    ): PlayerBalanceTransaction {
        if (! in_array($transaction->type, [
            PlayerBalanceTransaction::TYPE_OPENING_BALANCE,
            PlayerBalanceTransaction::TYPE_TOP_UP,
        ], true)) {
            throw new InvalidArgumentException('Match charges are edited through the game allocation screen.');
        }

        return $this->updateTransactionRecord($transaction, [
            'amount_cents' => $amountCents,
            'occurred_on' => $occurredOn,
            'updated_by' => $actor->id,
        ], $actor);
    }

    public function updateSettings(int $defaultMatchFeeCents, User $actor): FinancialSetting
    {
        $setting = FinancialSetting::current();
        $setting->update([
            'default_match_fee_cents' => $defaultMatchFeeCents,
            'updated_by' => $actor->id,
        ]);

        return $setting->fresh();
    }

    public function deleteGameCharges(Game $game, ?User $actor = null): void
    {
        $transactions = $game->balanceTransactions()
            ->where('type', PlayerBalanceTransaction::TYPE_MATCH_CHARGE)
            ->get();

        foreach ($transactions as $transaction) {
            $this->deleteTransactionRecord($transaction, $actor);
        }
    }

    public function transactionSnapshot(PlayerBalanceTransaction $transaction): array
    {
        return [
            'id' => $transaction->id,
            'player_id' => $transaction->player_id,
            'source_player_id' => $transaction->source_player_id,
            'game_id' => $transaction->game_id,
            'type' => $transaction->type,
            'amount_cents' => $transaction->amount_cents,
            'occurred_on' => $transaction->occurred_on?->toDateString(),
            'units' => $transaction->units,
            'unit_price_cents' => $transaction->unit_price_cents,
        ];
    }

    private function createTransactionRecord(array $attributes, ?User $actor): PlayerBalanceTransaction
    {
        $transaction = PlayerBalanceTransaction::create($attributes);

        FinancialAudit::create([
            'transaction_id' => $transaction->id,
            'actor_user_id' => $actor?->id,
            'action' => FinancialAudit::ACTION_CREATED,
            'before_values' => null,
            'after_values' => $this->transactionSnapshot($transaction),
        ]);

        return $transaction;
    }

    private function updateTransactionRecord(
        PlayerBalanceTransaction $transaction,
        array $attributes,
        ?User $actor,
    ): PlayerBalanceTransaction {
        $before = $this->transactionSnapshot($transaction);
        $transaction->update($attributes);
        $transaction = $transaction->fresh();

        if ($before !== $this->transactionSnapshot($transaction)) {
            FinancialAudit::create([
                'transaction_id' => $transaction->id,
                'actor_user_id' => $actor?->id,
                'action' => FinancialAudit::ACTION_UPDATED,
                'before_values' => $before,
                'after_values' => $this->transactionSnapshot($transaction),
            ]);
        }

        return $transaction;
    }

    private function deleteTransactionRecord(
        PlayerBalanceTransaction $transaction,
        ?User $actor,
    ): void {
        $before = $this->transactionSnapshot($transaction);
        $transactionId = $transaction->id;

        FinancialAudit::create([
            'transaction_id' => $transactionId,
            'actor_user_id' => $actor?->id,
            'action' => FinancialAudit::ACTION_DELETED,
            'before_values' => $before,
            'after_values' => null,
        ]);

        $transaction->delete();
    }

    private function gameDate(Game $game): string
    {
        $playedAt = $game->played_at;

        return $playedAt instanceof CarbonInterface
            ? $playedAt->toDateString()
            : Carbon::parse((string) $playedAt)->toDateString();
    }

    private function mappedValue(array $values, int $key, mixed $default): mixed
    {
        return array_key_exists($key, $values)
            ? $values[$key]
            : (array_key_exists((string) $key, $values) ? $values[(string) $key] : $default);
    }
}
