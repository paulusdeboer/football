<?php

namespace Tests\Unit\Services;

use App\Models\Player;
use App\Services\TeamBuilder;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class TeamBuilderTest extends TestCase
{
    public function test_it_assigns_every_player_to_one_of_two_balanced_teams(): void
    {
        $players = new Collection;
        foreach (range(1, 12) as $id) {
            $players->push((new Player)->forceFill([
                'id' => $id,
                'name' => "Player {$id}",
                'rating' => 700 + ($id * 10),
                'type' => $id % 3 === 0 ? 'defender' : ($id % 2 === 0 ? 'attacker' : 'both'),
            ]));
        }

        $teams = app(TeamBuilder::class)->build($players);

        $assignedIds = $teams['team1']->merge($teams['team2'])->pluck('id');

        $this->assertCount(6, $teams['team1']);
        $this->assertCount(6, $teams['team2']);
        $this->assertCount(12, $assignedIds->unique());
        $this->assertEqualsCanonicalizing(range(1, 12), $assignedIds->all());
        $this->assertLessThanOrEqual(20, abs($teams['team1']->sum('rating') - $teams['team2']->sum('rating')));
    }

    public function test_it_preserves_uneven_team_sizes_when_given_an_odd_number_of_players(): void
    {
        $players = Player::hydrate(collect(range(1, 11))->map(fn (int $id) => [
            'id' => $id,
            'name' => "Player {$id}",
            'rating' => 700,
            'type' => 'both',
        ])->all());

        $teams = app(TeamBuilder::class)->build($players);

        $this->assertCount(11, $teams['team1']->merge($teams['team2']));
        $this->assertLessThanOrEqual(1, abs($teams['team1']->count() - $teams['team2']->count()));
    }
}
