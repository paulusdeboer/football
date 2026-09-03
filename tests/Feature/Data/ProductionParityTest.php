<?php

namespace Tests\Feature\Data;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Read-only checks for the locally imported production snapshot.
 *
 * This suite is opt-in so normal tests can never connect to or alter the
 * imported snapshot. Run it inside the PHP container with RUN_PRODUCTION_PARITY=1.
 */
class ProductionParityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! config('parity.enabled')) {
            $this->markTestSkipped('Production parity checks are opt-in and read-only.');
        }

        $connection = config('parity.connection', 'mariadb');
        config(['database.default' => $connection]);
        DB::purge($connection);
        DB::reconnect($connection);
    }

    public function test_current_snapshot_has_the_documented_entity_counts(): void
    {
        $this->assertSame(90, DB::table('users')->count());
        $this->assertSame(89, DB::table('players')->count());
        $this->assertSame(70, DB::table('games')->count());
        $this->assertSame(834, DB::table('teams')->count());
        $this->assertSame(834, DB::table('game_player_ratings')->count());
        $this->assertSame(1458, DB::table('ratings')->count());
        $this->assertSame(210, DB::table('rating_requests')->count());
    }

    public function test_snapshot_anomalies_are_preserved_and_relationships_are_intact(): void
    {
        $this->assertSame(22, DB::table('players')->whereNotNull('deleted_at')->count());
        $this->assertSame(22, DB::table('users')->whereNotNull('deleted_at')->count());
        $this->assertSame(1, DB::table('users')->leftJoin('players', 'players.user_id', '=', 'users.id')
            ->whereNull('players.id')->count());
        $this->assertSame(2, DB::query()->fromSub(
            DB::table('teams')->select('game_id')->selectRaw("SUM(team = 'team1') as team1_count")
                ->selectRaw("SUM(team = 'team2') as team2_count")->groupBy('game_id'),
            'team_counts'
        )->whereRaw('team1_count <> team2_count')->count());
        $this->assertSame(0, DB::table('rating_requests')->select('game_id', 'player_id')
            ->groupBy('game_id', 'player_id')->havingRaw('COUNT(*) > 1')->count());
    }
}
