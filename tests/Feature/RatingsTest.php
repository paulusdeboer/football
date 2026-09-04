<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Player;
use App\Models\RatingRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class RatingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_rating_form_is_available_and_invalid_signatures_are_rejected(): void
    {
        [$game, $player, , $ratingRequest] = $this->gameWithPlayers();
        $url = $this->signedUrl('players.rate', $game, $ratingRequest);

        $this->get($url)->assertOk()->assertSee($player->name);
        $this->get($url.'&signature=invalid')->assertForbidden();
    }

    public function test_rating_form_exposes_a_valid_signed_submission_url(): void
    {
        [$game, $player, , $ratingRequest] = $this->gameWithPlayers();
        $url = $this->signedUrl('players.rate', $game, $ratingRequest);

        $this->get($url)->assertInertia(fn ($page) => $page
            ->component('Ratings/Form')
            ->where('storeUrl', fn (string $storeUrl): bool => URL::hasValidSignature(Request::create($storeUrl)))
        );
    }

    public function test_rating_submission_stores_decimal_values_and_blocks_duplicates(): void
    {
        [$game, $player, $ratedPlayers, $ratingRequest] = $this->gameWithPlayers();
        $url = $this->signedUrl('ratings.store', $game, $ratingRequest);
        $ratings = $ratedPlayers->mapWithKeys(fn ($ratedPlayer) => [$ratedPlayer->id => 8.5])->all();

        $first = $this->post($url, ['ratings' => $ratings]);

        $first->assertRedirect();
        $this->assertDatabaseCount('ratings', $ratedPlayers->count());
        $this->assertDatabaseHas('ratings', ['rating_player_id' => $player->id, 'rating_value' => 8.5]);
        $this->assertDatabaseHas('rating_requests', [
            'id' => $ratingRequest->id,
            'status' => RatingRequest::STATUS_COMPLETED,
        ]);

        $this->post($url, ['ratings' => $ratings])
            ->assertSessionHasErrors('rating');
    }

    public function test_expired_rating_request_is_rejected_and_marked_expired(): void
    {
        [$game, , , $ratingRequest] = $this->gameWithPlayers();
        $ratingRequest->update(['expires_at' => Carbon::now()->subMinute()]);
        $url = $this->signedUrl('players.rate', $game, $ratingRequest);

        $this->get($url)->assertForbidden();
        $this->assertDatabaseHas('rating_requests', [
            'id' => $ratingRequest->id,
            'status' => RatingRequest::STATUS_EXPIRED,
        ]);
    }

    /** @return array{Game, Player, Collection<int, Player>, RatingRequest} */
    private function gameWithPlayers(): array
    {
        $players = Player::factory()->count(10)->create();
        $game = Game::factory()->completed()->create();
        $game->teams()->attach(array_fill_keys($players->take(5)->modelKeys(), ['team' => 'team1']));
        $game->teams()->attach(array_fill_keys($players->skip(5)->modelKeys(), ['team' => 'team2']));

        foreach ($players as $player) {
            $game->gamePlayerRatings()->create(['player_id' => $player->id, 'rating' => 700, 'type' => $player->type]);
        }

        $ratingRequest = RatingRequest::create([
            'game_id' => $game->id,
            'player_id' => $players->first()->id,
            'status' => RatingRequest::STATUS_PENDING,
            'sent_at' => now(),
            'expires_at' => now()->addHours(72),
            'token_version' => 1,
        ]);

        return [$game, $players->first(), $players->skip(1)->values(), $ratingRequest];
    }

    private function signedUrl(string $route, Game $game, RatingRequest $ratingRequest): string
    {
        return URL::signedRoute($route, [
            'game' => $game->id,
            'ratingRequest' => $ratingRequest->id,
            'v' => $ratingRequest->token_version,
        ]);
    }
}
