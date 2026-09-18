<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use App\Models\PlayerBalanceTransaction;
use App\Services\FinanceService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class FinanceController extends Controller
{
    public function index(Request $request, FinanceService $finance): Response
    {
        $month = $this->requestedMonth($request->input('month'));
        $report = $this->report($month, $finance);

        return Inertia::render('Finance/Index', [
            'month' => $month->format('Y-m'),
            'defaultMatchFeeCents' => $finance->defaultMatchFeeCents(),
            'players' => $report['players'],
            'transactions' => $report['transactions'],
            'totals' => $report['totals'],
        ]);
    }

    public function topUps(): Response
    {
        return Inertia::render('Finance/TopUps', [
            'players' => $this->playerOptions(),
            'today' => now()->toDateString(),
        ]);
    }

    public function storeTopUps(Request $request, FinanceService $finance): RedirectResponse
    {
        $request->validate([
            'type' => ['required', Rule::in([
                PlayerBalanceTransaction::TYPE_TOP_UP,
                PlayerBalanceTransaction::TYPE_OPENING_BALANCE,
            ])],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.player_id' => ['required', 'integer', 'exists:players,id'],
            'entries.*.occurred_on' => ['required', 'date'],
            'entries.*.amount' => ['required'],
        ]);

        $actor = $request->user();
        $type = $request->string('type')->toString();

        DB::transaction(function () use ($request, $finance, $actor, $type): void {
            foreach ($request->input('entries', []) as $index => $entry) {
                $allowNegative = $type === PlayerBalanceTransaction::TYPE_OPENING_BALANCE;
                $amountCents = $this->parseAmount($finance, $entry['amount'] ?? null, "entries.{$index}.amount", $allowNegative);

                if ($type === PlayerBalanceTransaction::TYPE_TOP_UP && $amountCents <= 0) {
                    throw ValidationException::withMessages([
                        "entries.{$index}.amount" => __('A top-up must be greater than zero.'),
                    ]);
                }

                $finance->createManualTransaction(
                    (int) $entry['player_id'],
                    $type,
                    $amountCents,
                    (string) $entry['occurred_on'],
                    $actor,
                );
            }
        });

        return redirect()->route('finance.index')->with('success', __('Financial transactions saved successfully.'));
    }

    public function updateSettings(Request $request, FinanceService $finance): RedirectResponse
    {
        $request->validate(['default_match_fee' => ['required']]);
        $amountCents = $this->parseAmount($finance, $request->input('default_match_fee'), 'default_match_fee', true);

        if ($amountCents < 0) {
            throw ValidationException::withMessages([
                'default_match_fee' => __('The match fee cannot be negative.'),
            ]);
        }

        $finance->updateSettings($amountCents, $request->user());

        return back()->with('success', __('Financial settings saved successfully.'));
    }

    public function player(int $player, FinanceService $finance): Response
    {
        $playerModel = Player::withTrashed()->findOrFail($player);
        $transactions = $playerModel->balanceTransactions()
            ->with(['participant', 'game'])
            ->orderByDesc('occurred_on')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('Finance/Player', [
            'player' => [
                'id' => $playerModel->id,
                'name' => $playerModel->name,
                'inactive' => $playerModel->trashed(),
            ],
            'balanceCents' => $finance->balanceForPlayer($playerModel->id),
            'transactions' => $transactions->map(fn (PlayerBalanceTransaction $transaction) => $this->transactionData($transaction))->values()->all(),
        ]);
    }

    public function updateTransaction(
        Request $request,
        PlayerBalanceTransaction $transaction,
        FinanceService $finance,
    ): RedirectResponse {
        if (! in_array($transaction->type, [
            PlayerBalanceTransaction::TYPE_OPENING_BALANCE,
            PlayerBalanceTransaction::TYPE_TOP_UP,
        ], true)) {
            abort(403, __('Match charges are edited through the game allocation screen.'));
        }

        $request->validate([
            'occurred_on' => ['required', 'date'],
            'amount' => ['required'],
        ]);

        $amountCents = $this->parseAmount(
            $finance,
            $request->input('amount'),
            'amount',
            $transaction->type === PlayerBalanceTransaction::TYPE_OPENING_BALANCE,
        );

        if ($transaction->type === PlayerBalanceTransaction::TYPE_TOP_UP && $amountCents <= 0) {
            throw ValidationException::withMessages(['amount' => __('A top-up must be greater than zero.')]);
        }

        $finance->updateManualTransaction(
            $transaction,
            $amountCents,
            (string) $request->input('occurred_on'),
            $request->user(),
        );

        return back()->with('success', __('Financial transaction updated successfully.'));
    }

    public function gameCharges(int $game, FinanceService $finance): Response
    {
        $gameModel = Game::findOrFail($game);
        $participants = $gameModel->teams()
            ->orderBy('name')
            ->get(['players.id', 'players.name']);
        $transactions = $gameModel->balanceTransactions()
            ->where('type', PlayerBalanceTransaction::TYPE_MATCH_CHARGE)
            ->with('player')
            ->get()
            ->keyBy('source_player_id');

        return Inertia::render('Finance/GameCharges', [
            'game' => [
                'id' => $gameModel->id,
                'played_at' => $gameModel->played_at,
                'fee_cents' => $gameModel->fee_cents,
            ],
            'defaultMatchFeeCents' => $finance->defaultMatchFeeCents(),
            'players' => $this->playerOptions(true),
            'participants' => $participants->map(function (Player $participant) use ($transactions): array {
                $transaction = $transactions->get($participant->id);

                return [
                    'id' => $participant->id,
                    'name' => $participant->name,
                    'account_player_id' => $transaction?->player_id ?? $participant->id,
                    'units' => $transaction?->units ?? 1,
                ];
            })->values()->all(),
        ]);
    }

    public function updateGameCharges(
        Request $request,
        int $game,
        FinanceService $finance,
    ): RedirectResponse {
        $gameModel = Game::findOrFail($game);
        $request->validate([
            'fee' => ['required'],
            'charge_accounts' => ['nullable', 'array'],
            'charge_accounts.*' => ['required', 'integer', 'exists:players,id'],
            'charge_units' => ['nullable', 'array'],
            'charge_units.*' => ['required', 'integer', 'min:0'],
        ]);

        $feeCents = $this->parseAmount($finance, $request->input('fee'), 'fee', true);
        if ($feeCents < 0) {
            throw ValidationException::withMessages(['fee' => __('The match fee cannot be negative.')]);
        }

        $participants = $gameModel->teams()->get();
        DB::transaction(function () use ($gameModel, $participants, $request, $finance, $feeCents): void {
            $gameModel->update(['fee_cents' => $feeCents]);
            $finance->syncGameCharges(
                $gameModel,
                $participants,
                $request->input('charge_accounts', []),
                $request->input('charge_units', []),
                $feeCents,
                $request->user(),
            );
        });

        return redirect()->route('finance.games.charges.edit', $gameModel)->with('success', __('Game charges updated successfully.'));
    }

    public function export(Request $request, FinanceService $finance)
    {
        $month = $this->requestedMonth($request->input('month'));
        $report = $this->report($month, $finance);
        $filename = "financial-overview-{$month->format('Y-m')}.csv";

        return response()->streamDownload(function () use ($report): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                __('Player'),
                __('Opening balance'),
                __('Opening movements'),
                __('Top-ups'),
                __('Game charges'),
                __('Closing balance'),
                __('Current balance'),
                __('Status'),
            ], ';');

            foreach ($report['players'] as $player) {
                fputcsv($output, [
                    $player['name'],
                    $this->formatAmount($player['opening_balance_cents']),
                    $this->formatAmount($player['opening_movements_cents']),
                    $this->formatAmount($player['topups_cents']),
                    $this->formatAmount($player['charges_cents']),
                    $this->formatAmount($player['closing_balance_cents']),
                    $this->formatAmount($player['current_balance_cents']),
                    $player['status'],
                ], ';');
            }

            fputcsv($output, [
                __('Total'),
                $this->formatAmount($report['totals']['opening_balance_cents']),
                $this->formatAmount($report['totals']['opening_movements_cents']),
                $this->formatAmount($report['totals']['topups_cents']),
                $this->formatAmount($report['totals']['charges_cents']),
                $this->formatAmount($report['totals']['closing_balance_cents']),
                $this->formatAmount($report['totals']['current_balance_cents']),
                '',
            ], ';');

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function report(Carbon $month, FinanceService $finance): array
    {
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();
        $startDate = $start->toDateString();
        $endDate = $end->toDateString();
        $today = now()->toDateString();
        $defaultFeeCents = $finance->defaultMatchFeeCents();
        $transactions = PlayerBalanceTransaction::query()
            ->with(['player', 'participant', 'game'])
            ->get();

        $players = Player::withTrashed()->orderBy('name')->get();
        $rows = $players->map(function (Player $player) use ($transactions, $startDate, $endDate, $today, $defaultFeeCents): array {
            $playerTransactions = $transactions->where('player_id', $player->id);
            $before = $playerTransactions->filter(fn ($transaction): bool => $transaction->occurred_on?->toDateString() < $startDate);
            $inMonth = $playerTransactions->filter(fn ($transaction): bool => $transaction->occurred_on?->toDateString() >= $startDate
                && $transaction->occurred_on?->toDateString() <= $endDate);
            $openingTransactions = $before->merge($inMonth->filter(fn ($transaction): bool =>
                $transaction->type === PlayerBalanceTransaction::TYPE_OPENING_BALANCE
                && $transaction->occurred_on?->toDateString() === $startDate
            ));
            $periodMovements = $inMonth->reject(fn ($transaction): bool =>
                $transaction->type === PlayerBalanceTransaction::TYPE_OPENING_BALANCE
                && $transaction->occurred_on?->toDateString() === $startDate
            );
            $asOfToday = $playerTransactions->filter(fn ($transaction): bool => $transaction->occurred_on?->toDateString() <= $today);

            $openingBalance = (int) $openingTransactions->sum('amount_cents');
            $openingMovements = (int) $periodMovements->where('type', PlayerBalanceTransaction::TYPE_OPENING_BALANCE)->sum('amount_cents');
            $topUps = (int) $periodMovements->where('type', PlayerBalanceTransaction::TYPE_TOP_UP)->sum('amount_cents');
            $charges = abs((int) $periodMovements->where('type', PlayerBalanceTransaction::TYPE_MATCH_CHARGE)->sum('amount_cents'));
            $closingBalance = $openingBalance + (int) $periodMovements->sum('amount_cents');
            $currentBalance = (int) $asOfToday->sum('amount_cents');
            $status = $closingBalance < 0
                ? 'negative'
                : ($defaultFeeCents > 0 && $closingBalance < $defaultFeeCents ? 'low' : 'ok');

            return [
                'id' => $player->id,
                'name' => $player->name,
                'inactive' => $player->trashed(),
                'opening_balance_cents' => $openingBalance,
                'opening_movements_cents' => $openingMovements,
                'topups_cents' => $topUps,
                'charges_cents' => $charges,
                'closing_balance_cents' => $closingBalance,
                'current_balance_cents' => $currentBalance,
                'transactions_count' => $inMonth->count(),
                'status' => $status,
            ];
        })->values()->all();

        $monthTransactions = $transactions
            ->filter(fn ($transaction): bool => $transaction->occurred_on?->toDateString() >= $startDate
                && $transaction->occurred_on?->toDateString() <= $endDate)
            ->sortByDesc(fn ($transaction) => [$transaction->occurred_on?->timestamp ?? 0, $transaction->id])
            ->map(fn (PlayerBalanceTransaction $transaction) => $this->transactionData($transaction))
            ->values()
            ->all();

        return [
            'players' => $rows,
            'transactions' => $monthTransactions,
            'totals' => [
                'opening_balance_cents' => array_sum(array_column($rows, 'opening_balance_cents')),
                'opening_movements_cents' => array_sum(array_column($rows, 'opening_movements_cents')),
                'topups_cents' => array_sum(array_column($rows, 'topups_cents')),
                'charges_cents' => array_sum(array_column($rows, 'charges_cents')),
                'closing_balance_cents' => array_sum(array_column($rows, 'closing_balance_cents')),
                'current_balance_cents' => array_sum(array_column($rows, 'current_balance_cents')),
            ],
        ];
    }

    private function transactionData(PlayerBalanceTransaction $transaction): array
    {
        return [
            'id' => $transaction->id,
            'player_id' => $transaction->player_id,
            'player_name' => $transaction->player?->name,
            'participant_name' => $transaction->participant?->name,
            'game_id' => $transaction->game_id,
            'game_date' => $transaction->game?->played_at
                ? Carbon::parse($transaction->game->played_at)->toDateString()
                : null,
            'type' => $transaction->type,
            'amount_cents' => $transaction->amount_cents,
            'occurred_on' => $transaction->occurred_on?->toDateString(),
            'units' => $transaction->units,
            'unit_price_cents' => $transaction->unit_price_cents,
            'editable' => in_array($transaction->type, [
                PlayerBalanceTransaction::TYPE_OPENING_BALANCE,
                PlayerBalanceTransaction::TYPE_TOP_UP,
            ], true),
        ];
    }

    private function playerOptions(bool $includeTrashed = false): array
    {
        $query = $includeTrashed ? Player::withTrashed() : Player::query();

        return $query->orderBy('name')->get(['id', 'name'])->map(fn (Player $player): array => [
            'id' => $player->id,
            'name' => $player->name,
        ])->values()->all();
    }

    private function requestedMonth(mixed $value): Carbon
    {
        $value = is_string($value) && preg_match('/^\d{4}-\d{2}$/', $value) ? $value : now()->format('Y-m');
        try {
            $month = Carbon::createFromFormat('!Y-m', $value);
        } catch (\Throwable) {
            return now()->startOfMonth();
        }

        return $month->format('Y-m') === $value ? $month : now()->startOfMonth();
    }

    private function parseAmount(FinanceService $finance, mixed $value, string $field, bool $allowNegative = false): int
    {
        try {
            return $finance->parseAmountToCents($value, $allowNegative);
        } catch (InvalidArgumentException) {
            throw ValidationException::withMessages([
                $field => __('Enter a valid amount with at most two decimals.'),
            ]);
        }
    }

    private function formatAmount(int $amountCents): string
    {
        return number_format($amountCents / 100, 2, ',', '');
    }
}
