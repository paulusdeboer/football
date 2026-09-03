<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class RatingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_rating_form_is_available_and_invalid_signatures_are_rejected(): void
    {
        [$game, $player] = $this->gameWithPlayers();
        $url = URL::signedRoute('players.rate', ['game' => $game->id, 'player' => $player->id]);

        $this->get($url)->assertOk()->assertSee($player->name);
        $this->get($url.'&signature=invalid')->assertForbidden();
    }

    public function test_rating_form_exposes_a_valid_signed_submission_url(): void
    {
        [$game, $player] = $this->gameWithPlayers();
        $url = URL::signedRoute('players.rate', ['game' => $game->id, 'player' => $player->id]);

        $this->get($url)->assertInertia(fn ($page) => $page
            ->component('Ratings/Form')
            ->where('storeUrl', fn (string $storeUrl): bool => URL::hasValidSignature(Request::create($storeUrl)))
        );
    }

    public function test_rating_submission_stores_decimal_values_and_blocks_duplicates(): void
    {
        [$game, $player, $ratedPlayers] = $this->gameWithPlayers();
        $url = URL::signedRoute('ratings.store', ['game' => $game->id, 'player' => $player->id]);
        $ratings = $ratedPlayers->mapWithKeys(fn ($ratedPlayer) => [$ratedPlayer->id => 8.5])->all();

        $first = $this->post($url, ['ratings' => $ratings]);

        $first->assertRedirect();
        $this->assertDatabaseCount('ratings', $ratedPlayers->count());
        $this->assertDatabaseHas('ratings', ['rating_player_id' => $player->id, 'rating_value' => 8.5]);

        $this->post($url, ['ratings' => $ratings])
            ->assertSessionHasErrors('rating');
    }

    /** @return array{Game, Player, \Illuminate\Database\Eloquent\Collection<int, Player>} */
    private function gameWithPlayers(): array
    {
        $players = Player::factory()->count(10)->create();
        $game = Game::factory()->completed()->create();
        $game->teams()->attach(array_fill_keys($players->take(5)->modelKeys(), ['team' => 'team1']));
        $game->teams()->attach(array_fill_keys($players->skip(5)->modelKeys(), ['team' => 'team2']));

        foreach ($players as $player) {
            $game->gamePlayerRatings()->create(['player_id' => $player->id, 'rating' => 700, 'type' => $player->type]);
        }

        return [$game, $players->first(), $players->skip(1)->values()];
    }
}
