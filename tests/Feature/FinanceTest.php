<?php

namespace Tests\Feature;

use App\Models\FinancialAudit;
use App\Models\FinancialSetting;
use App\Models\Game;
use App\Models\Player;
use App\Models\PlayerBalanceTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_game_registration_creates_one_financial_charge_per_participant(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        FinancialSetting::current()->update(['default_match_fee_cents' => 500]);
        $players = Player::factory()->count(10)->create();

        $this->post('/games', [
            'played_at' => '2026-09-11 20:00',
            'players' => $players->modelKeys(),
            'fee' => '5.00',
        ])->assertRedirect();

        $game = Game::firstOrFail();
        $this->assertSame(500, $game->fee_cents);
        $this->assertSame(10, PlayerBalanceTransaction::where('game_id', $game->id)->count());
        $this->assertSame(-5000, (int) PlayerBalanceTransaction::where('game_id', $game->id)->sum('amount_cents'));
    }

    public function test_cash_arrangement_charges_two_units_to_the_other_account(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        FinancialSetting::current()->update(['default_match_fee_cents' => 500]);
        $players = Player::factory()->count(10)->create();
        $participantA = $players[0];
        $accountB = $players[1];

        $this->post('/games', [
            'played_at' => '2026-09-11 20:00',
            'players' => $players->modelKeys(),
            'charge_accounts' => [(string) $participantA->id => $accountB->id],
        ])->assertRedirect();

        $game = Game::firstOrFail();
        $bCharges = PlayerBalanceTransaction::where('game_id', $game->id)
            ->where('player_id', $accountB->id)
            ->get();

        $this->assertSame(2, $bCharges->count());
        $this->assertSame(2, (int) $bCharges->sum('units'));
        $this->assertSame(-1000, (int) $bCharges->sum('amount_cents'));
        $this->assertDatabaseMissing('player_balance_transactions', [
            'game_id' => $game->id,
            'player_id' => $participantA->id,
            'source_player_id' => $participantA->id,
        ]);
    }

    public function test_updating_a_game_synchronizes_charges_without_duplicates(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        FinancialSetting::current()->update(['default_match_fee_cents' => 500]);
        $players = Player::factory()->count(10)->create();
        $payload = [
            'played_at' => '2026-09-11 20:00',
            'players' => $players->modelKeys(),
        ];

        $this->post('/games', $payload)->assertRedirect();
        $game = Game::firstOrFail();
        $originalIds = PlayerBalanceTransaction::where('game_id', $game->id)->pluck('id')->sort()->values()->all();

        $this->put("/games/{$game->id}", [
            ...$payload,
            'charge_accounts' => [(string) $players[0]->id => $players[1]->id],
        ])->assertRedirect("/games/{$game->id}");

        $this->assertSame(10, PlayerBalanceTransaction::where('game_id', $game->id)->count());
        $this->assertSame(-5000, (int) PlayerBalanceTransaction::where('game_id', $game->id)->sum('amount_cents'));
        $this->assertSame($originalIds, PlayerBalanceTransaction::where('game_id', $game->id)->pluck('id')->sort()->values()->all());
    }

    public function test_game_fee_is_snapshotted_when_the_default_changes(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        FinancialSetting::current()->update(['default_match_fee_cents' => 500]);
        $players = Player::factory()->count(10)->create();

        $this->post('/games', [
            'played_at' => '2026-09-11 20:00',
            'players' => $players->modelKeys(),
        ])->assertRedirect();
        $game = Game::firstOrFail();

        FinancialSetting::current()->update(['default_match_fee_cents' => 700]);

        $this->assertSame(500, $game->fresh()->fee_cents);
        $this->assertSame(-500, (int) PlayerBalanceTransaction::where('game_id', $game->id)->firstOrFail()->amount_cents);
    }

    public function test_editing_without_a_fee_keeps_the_existing_game_snapshot(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        FinancialSetting::current()->update(['default_match_fee_cents' => 500]);
        $players = Player::factory()->count(10)->create();

        $this->post('/games', [
            'played_at' => '2026-09-11 20:00',
            'players' => $players->modelKeys(),
        ])->assertRedirect();
        $game = Game::firstOrFail();

        FinancialSetting::current()->update(['default_match_fee_cents' => 700]);
        $this->put("/games/{$game->id}", [
            'played_at' => '2026-09-12 20:00',
            'players' => $players->modelKeys(),
        ])->assertRedirect();

        $this->assertSame(500, $game->fresh()->fee_cents);
        $this->assertSame(-5000, (int) PlayerBalanceTransaction::where('game_id', $game->id)->sum('amount_cents'));
    }

    public function test_finance_manager_can_enter_topups_and_view_a_month_report(): void
    {
        $financeUser = User::factory()->finance()->create(['name' => 'Finance user', 'password' => bcrypt('secret')]);
        $player = Player::factory()->create();
        $this->actingAs($financeUser);

        $this->post('/finance/top-ups', [
            'type' => PlayerBalanceTransaction::TYPE_TOP_UP,
            'entries' => [[
                'player_id' => $player->id,
                'occurred_on' => '2026-09-01',
                'amount' => '20.00',
            ]],
        ])->assertRedirect(route('finance.index'));

        $this->get('/finance?month=2026-09')
            ->assertInertia(fn ($page) => $page
                ->component('Finance/Index')
                ->where('players.0.id', $player->id)
                ->where('players.0.topups_cents', 2000)
                ->where('players.0.closing_balance_cents', 2000)
                ->where('totals.topups_cents', 2000)
            );
    }

    public function test_opening_balance_on_the_month_start_is_reported_as_the_opening_balance(): void
    {
        $financeUser = User::factory()->finance()->create();
        $player = Player::factory()->create();
        $this->actingAs($financeUser);

        $this->post('/finance/top-ups', [
            'type' => PlayerBalanceTransaction::TYPE_OPENING_BALANCE,
            'entries' => [[
                'player_id' => $player->id,
                'occurred_on' => '2026-09-01',
                'amount' => '15.00',
            ]],
        ])->assertRedirect();

        $this->get('/finance?month=2026-09')
            ->assertInertia(fn ($page) => $page
                ->where('players.0.opening_balance_cents', 1500)
                ->where('players.0.opening_movements_cents', 0)
                ->where('players.0.closing_balance_cents', 1500)
            );
    }

    public function test_month_report_marks_negative_and_low_closing_balances(): void
    {
        $financeUser = User::factory()->finance()->create();
        $negativePlayer = Player::factory()->create(['name' => 'A negative player']);
        $lowPlayer = Player::factory()->create(['name' => 'B low player']);
        $this->actingAs($financeUser);
        FinancialSetting::current()->update(['default_match_fee_cents' => 500]);

        $this->post('/finance/top-ups', [
            'type' => PlayerBalanceTransaction::TYPE_OPENING_BALANCE,
            'entries' => [
                ['player_id' => $negativePlayer->id, 'occurred_on' => '2026-09-01', 'amount' => '-1.00'],
                ['player_id' => $lowPlayer->id, 'occurred_on' => '2026-09-01', 'amount' => '3.00'],
            ],
        ])->assertRedirect();

        $this->get('/finance?month=2026-09')
            ->assertInertia(fn ($page) => $page
                ->where('players.0.status', 'negative')
                ->where('players.1.status', 'low')
            );
    }

    public function test_finance_manager_can_edit_a_topup_and_the_change_is_audited(): void
    {
        $financeUser = User::factory()->finance()->create();
        $player = Player::factory()->create();
        $otherPlayer = Player::factory()->create();
        $this->actingAs($financeUser);

        $this->post('/finance/top-ups', [
            'type' => PlayerBalanceTransaction::TYPE_TOP_UP,
            'entries' => [[
                'player_id' => $player->id,
                'occurred_on' => '2026-09-01',
                'amount' => '20.00',
            ]],
        ]);
        $transaction = PlayerBalanceTransaction::firstOrFail();

        $this->put("/finance/transactions/{$transaction->id}", [
            'player_id' => $otherPlayer->id,
            'occurred_on' => '2026-09-02',
            'amount' => '25.00',
        ])->assertRedirect();

        $this->assertSame(2500, $transaction->fresh()->amount_cents);
        $this->assertSame($player->id, $transaction->fresh()->player_id);
        $this->assertDatabaseHas('financial_audits', [
            'transaction_id' => $transaction->id,
            'actor_user_id' => $financeUser->id,
            'action' => FinancialAudit::ACTION_UPDATED,
        ]);
    }

    public function test_finance_manager_can_correct_game_charge_allocation(): void
    {
        $admin = User::factory()->admin()->create();
        $players = Player::factory()->count(10)->create();
        $this->actingAs($admin);
        FinancialSetting::current()->update(['default_match_fee_cents' => 500]);

        $this->post('/games', [
            'played_at' => '2026-09-11 20:00',
            'players' => $players->modelKeys(),
        ])->assertRedirect();
        $game = Game::firstOrFail();

        $financeUser = User::factory()->finance()->create();
        $this->actingAs($financeUser);
        $this->put("/finance/games/{$game->id}/charges", [
            'fee' => '5.00',
            'charge_accounts' => [(string) $players[0]->id => $players[1]->id],
            'charge_units' => [(string) $players[0]->id => 1],
        ])->assertRedirect("/finance/games/{$game->id}/charges");

        $this->assertDatabaseHas('player_balance_transactions', [
            'game_id' => $game->id,
            'source_player_id' => $players[0]->id,
            'player_id' => $players[1]->id,
            'amount_cents' => -500,
        ]);
        $this->assertDatabaseHas('financial_audits', [
            'actor_user_id' => $financeUser->id,
            'action' => FinancialAudit::ACTION_UPDATED,
        ]);
    }

    public function test_deleting_an_open_game_removes_its_financial_charges(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        FinancialSetting::current()->update(['default_match_fee_cents' => 500]);
        $players = Player::factory()->count(10)->create();

        $this->post('/games', [
            'played_at' => '2026-09-11 20:00',
            'players' => $players->modelKeys(),
        ]);
        $game = Game::firstOrFail();
        $this->assertSame(10, PlayerBalanceTransaction::where('game_id', $game->id)->count());

        $this->delete("/games/{$game->id}")->assertRedirect('/games');

        $this->assertDatabaseMissing('games', ['id' => $game->id]);
        $this->assertDatabaseMissing('player_balance_transactions', ['game_id' => $game->id]);
        $this->assertDatabaseHas('financial_audits', ['action' => FinancialAudit::ACTION_DELETED]);
    }

    public function test_admin_can_assign_the_finance_role(): void
    {
        $admin = User::factory()->admin()->create();
        $player = Player::factory()->create();
        $this->actingAs($admin);

        $this->put("/players/{$player->id}", [
            'name' => $player->name,
            'username' => $player->user->name,
            'email' => $player->user->email,
            'rating' => $player->rating / 100,
            'type' => $player->type,
            'role' => User::ROLE_FINANCE,
        ])->assertRedirect('/players');

        $this->assertSame(User::ROLE_FINANCE, $player->user->fresh()->role);
    }

    public function test_finance_manager_can_log_in_but_cannot_manage_games_or_access_player_overview(): void
    {
        $financeUser = User::factory()->finance()->create(['name' => 'Finance login', 'password' => bcrypt('secret')]);

        $this->post('/login', [
            'name' => 'Finance login',
            'password' => 'secret',
        ])->assertRedirect('/finance');
        $this->assertAuthenticatedAs($financeUser);

        $this->get('/games')->assertSuccessful();
        $this->get('/players')->assertForbidden();

        $game = Game::factory()->create();
        $player = Player::factory()->create();

        $this->post('/games', [
            'played_at' => '2026-09-11 20:00',
            'players' => Player::factory()->count(10)->create()->modelKeys(),
        ])->assertForbidden();

        $this->put("/games/{$game->id}", [])->assertForbidden();
        $this->put("/players/{$player->id}", [])->assertForbidden();
        $this->get("/games/{$game->id}/enter-result")->assertForbidden();
        $this->post("/games/{$game->id}/results", [])->assertForbidden();
        $this->get("/players/{$player->id}/edit")->assertForbidden();
        $this->get('/settings/whatsapp')->assertForbidden();
    }

    public function test_editing_a_pre_finance_game_does_not_create_historical_charges(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $players = Player::factory()->count(10)->create();
        $game = Game::factory()->create(['played_at' => '2026-09-01', 'fee_cents' => null]);
        $game->teams()->sync($players->mapWithKeys(fn ($player): array => [$player->id => ['team' => 'team1']])->all());

        $this->put("/games/{$game->id}", [
            'played_at' => '2026-09-02',
            'players' => $players->modelKeys(),
            'fee' => '5.00',
        ])->assertRedirect();

        $this->assertSame(0, PlayerBalanceTransaction::where('game_id', $game->id)->count());
        $this->assertNull($game->fresh()->fee_cents);
    }

    public function test_players_cannot_access_financial_pages(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/finance')->assertForbidden();
        $this->post('/finance/top-ups')->assertForbidden();
    }

    public function test_financial_csv_export_contains_player_balances(): void
    {
        $this->actingAs(User::factory()->finance()->create());
        $player = Player::factory()->create(['name' => 'CSV Player']);

        $this->post('/finance/top-ups', [
            'type' => PlayerBalanceTransaction::TYPE_TOP_UP,
            'entries' => [[
                'player_id' => $player->id,
                'occurred_on' => '2026-09-01',
                'amount' => '12.50',
            ]],
        ]);

        $response = $this->get('/finance/export?month=2026-09');
        $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('CSV Player', $response->streamedContent());
        $this->assertStringContainsString('Totaal', $response->streamedContent());
    }
}
