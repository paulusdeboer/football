<?php

namespace Tests\Feature;

use App\Mail\RatingRequestMail;
use App\Models\Game;
use App\Models\Player;
use App\Models\RatingRequest;
use App\Models\User;
use App\Services\RatingRequestService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class GamesTest extends TestCase
{
    use RefreshDatabase;

    public function test_game_creation_persists_snapshots_and_balanced_teams(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $players = Player::factory()->count(10)->create();

        $response = $this->post('/games', [
            'played_at' => '2026-08-28 20:00',
            'players' => $players->pluck('id')->all(),
        ]);

        $game = Game::firstOrFail();
        $response->assertRedirect("/games/{$game->id}");
        $this->assertCount(10, $game->teams);
        $this->assertDatabaseCount('game_player_ratings', 10);
        $this->assertSame(5, $game->teams->where('pivot.team', 'team1')->count());
        $this->assertSame(5, $game->teams->where('pivot.team', 'team2')->count());
    }

    public function test_result_submission_recalculates_player_ratings_and_can_send_three_requests(): void
    {
        \Mail::fake();
        $this->actingAs(User::factory()->admin()->create());
        $players = Player::factory()->count(10)->create(['rating' => 700]);
        $game = Game::factory()->completed()->create(['played_at' => now()->addDay()]);
        $game->teams()->attach(array_fill_keys($players->take(5)->modelKeys(), ['team' => 'team1']));
        $game->teams()->attach(array_fill_keys($players->skip(5)->modelKeys(), ['team' => 'team2']));

        foreach ($players as $player) {
            $game->gamePlayerRatings()->create(['player_id' => $player->id, 'rating' => 700, 'type' => $player->type]);
        }

        $this->post("/games/{$game->id}/results", [
            'team1_score' => 4,
            'team2_score' => 1,
            'send_rating_requests' => true,
        ])->assertRedirect("/games/{$game->id}");

        $this->assertSame(4, $game->fresh()->team1_score);
        $this->assertNotSame(700, Player::findOrFail($players->first()->id)->rating);
        $this->assertDatabaseCount('rating_requests', 3);
        \Mail::assertSent(RatingRequestMail::class, 3);
    }

    public function test_rating_request_uses_the_branded_mail_template(): void
    {
        $game = Game::factory()->completed()->create(['played_at' => '2026-08-28 20:00']);
        $mail = new RatingRequestMail($game, 'https://example.test/ratings/abc');
        $html = (string) $mail->render();

        $this->assertStringContainsString('Vrijdag voetbal', $html);
        $this->assertStringContainsString('Beoordeling gevraagd', $html);
        $this->assertStringContainsString('Beoordeel spelers', $html);
        $this->assertStringContainsString('28-08-2026', $html);
        $this->assertStringContainsString('favicon.svg', $html);
        $this->assertStringNotContainsString('Laravel', $html);
        $this->assertSame('noreply@vrijdagvoetbal.nl', $mail->from[0]['address']);
        $this->assertSame('Vrijdag voetbal', $mail->from[0]['name']);
    }

    public function test_admin_can_resend_a_request_and_invalidate_the_previous_link(): void
    {
        \Mail::fake();
        $this->actingAs(User::factory()->admin()->create());
        [$game, $requests] = $this->gameWithRatingRequests(1);
        $ratingRequest = $requests->first();
        $oldUrl = URL::temporarySignedRoute('players.rate', $ratingRequest->expires_at, [
            'game' => $game->id,
            'ratingRequest' => $ratingRequest->id,
            'v' => $ratingRequest->token_version,
        ]);

        $this->post(route('rating-requests.resend', [$game, $ratingRequest]))
            ->assertRedirect("/games/{$game->id}");

        $fresh = $ratingRequest->fresh();
        $this->assertSame(2, $fresh->token_version);
        $this->assertSame(RatingRequest::STATUS_PENDING, $fresh->status);
        $this->assertDatabaseHas('rating_request_events', [
            'rating_request_id' => $fresh->id,
            'type' => 'resend',
        ]);
        $this->get("/games/{$game->id}")->assertInertia(fn ($page) => $page
            ->where('ratingRequests.0.history.0.type', 'initial_send')
            ->has('ratingRequests.0.history', 1)
        );
        $this->get($oldUrl)->assertForbidden();
        \Mail::assertSent(RatingRequestMail::class, 1);
    }

    public function test_game_detail_hides_history_when_only_the_initial_send_exists(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        [$game] = $this->gameWithRatingRequests(1);

        $this->get("/games/{$game->id}")->assertInertia(fn ($page) => $page
            ->where('ratingRequests.0.history', [])
        );
    }

    public function test_game_detail_keeps_completion_time_out_of_request_history(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        [$game, $requests] = $this->gameWithRatingRequests(1);

        app(RatingRequestService::class)->markCompleted($requests->first());

        $this->get("/games/{$game->id}")->assertInertia(fn ($page) => $page
            ->where('ratingRequests.0.status', RatingRequest::STATUS_COMPLETED)
            ->where('ratingRequests.0.completed_at', fn ($completedAt): bool => $completedAt !== null)
            ->where('ratingRequests.0.history', [])
        );
    }

    public function test_admin_can_replace_a_request_with_a_chosen_player(): void
    {
        \Mail::fake();
        $this->actingAs(User::factory()->admin()->create());
        [$game, $requests, $players] = $this->gameWithRatingRequests(1);
        $oldRequest = $requests->first();
        $replacement = $players[1];

        $this->post(route('rating-requests.replace', [$game, $oldRequest]), [
            'player_id' => $replacement->id,
        ])->assertRedirect("/games/{$game->id}");

        $this->assertSame(RatingRequest::STATUS_REVOKED, $oldRequest->fresh()->status);
        $newRequest = RatingRequest::where('replacement_of_id', $oldRequest->id)->firstOrFail();
        $this->assertSame($replacement->id, $newRequest->player_id);
        $this->assertSame(RatingRequest::STATUS_PENDING, $newRequest->status);
        $this->assertDatabaseHas('rating_request_events', [
            'rating_request_id' => $oldRequest->id,
            'type' => 'replace',
            'previous_player_id' => $oldRequest->player_id,
            'new_player_id' => $replacement->id,
        ]);
    }

    public function test_admin_can_replace_a_request_with_a_random_suitable_player(): void
    {
        \Mail::fake();
        $this->actingAs(User::factory()->admin()->create());
        [$game, $requests, $players] = $this->gameWithRatingRequests(1);
        $oldRequest = $requests->first();

        $this->post(route('rating-requests.replace', [$game, $oldRequest]))
            ->assertRedirect("/games/{$game->id}");

        $newRequest = RatingRequest::where('replacement_of_id', $oldRequest->id)->firstOrFail();
        $this->assertContains($newRequest->player_id, $players->skip(1)->modelKeys());
        $this->assertNotSame($oldRequest->player_id, $newRequest->player_id);
    }

    public function test_non_admin_cannot_manage_rating_requests(): void
    {
        $this->actingAs(User::factory()->create());
        [$game, $requests] = $this->gameWithRatingRequests(1);

        $this->post(route('rating-requests.resend', [$game, $requests->first()]))
            ->assertForbidden();
    }

    public function test_request_cannot_be_resent_or_replaced_after_player_submitted_a_rating(): void
    {
        \Mail::fake();
        $this->actingAs(User::factory()->admin()->create());
        [$game, $requests, $players] = $this->gameWithRatingRequests(1);
        $ratingRequest = $requests->first();
        $game->ratings()->create([
            'rating_player_id' => $ratingRequest->player_id,
            'rated_player_id' => $players[1]->id,
            'rating_value' => 8,
        ]);

        $this->get("/games/{$game->id}")->assertInertia(fn ($page) => $page
            ->where('ratingRequests.0.has_submitted_rating', true)
        );

        $this->post(route('rating-requests.resend', [$game, $ratingRequest]))
            ->assertStatus(422);
        $this->post(route('rating-requests.replace', [$game, $ratingRequest]), [
            'player_id' => $players[1]->id,
        ])->assertStatus(422);

        $this->assertSame(1, $ratingRequest->fresh()->token_version);
        $this->assertSame(RatingRequest::STATUS_PENDING, $ratingRequest->fresh()->status);
        $this->assertSame(1, RatingRequest::where('game_id', $game->id)->count());
        \Mail::assertNothingSent();
    }

    public function test_random_replacement_fails_when_no_suitable_player_exists(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        [$game, $requests, $players] = $this->gameWithRatingRequests(10);
        $oldRequest = $requests->first();

        $this->post(route('rating-requests.replace', [$game, $oldRequest]))
            ->assertSessionHasErrors('player_id');

        $this->assertSame(RatingRequest::STATUS_PENDING, $oldRequest->fresh()->status);
        $this->assertSame(10, RatingRequest::where('game_id', $game->id)->count());
    }

    public function test_requests_for_non_latest_completed_games_cannot_be_resent_or_replaced(): void
    {
        \Mail::fake();
        $this->actingAs(User::factory()->admin()->create());
        [$game, $requests, $players] = $this->gameWithRatingRequests(1);
        $game->update(['played_at' => now()->subMinute()]);
        Game::factory()->completed()->create(['played_at' => now()->addMinute()]);
        $ratingRequest = $requests->first();

        $this->post(route('rating-requests.resend', [$game, $ratingRequest]))
            ->assertStatus(422);
        $this->post(route('rating-requests.replace', [$game, $ratingRequest]), [
            'player_id' => $players[1]->id,
        ])->assertStatus(422);

        $this->assertSame(1, $ratingRequest->fresh()->token_version);
        $this->assertSame(RatingRequest::STATUS_PENDING, $ratingRequest->fresh()->status);
        $this->assertSame(1, RatingRequest::where('game_id', $game->id)->count());
        \Mail::assertNothingSent();
    }

    public function test_latest_completed_game_can_manage_rating_requests(): void
    {
        \Mail::fake();
        $this->actingAs(User::factory()->admin()->create());
        [$game, $requests] = $this->gameWithRatingRequests(1);
        $game->update(['played_at' => now()->subMinute()]);

        $this->post(route('rating-requests.resend', [$game, $requests->first()]))
            ->assertRedirect("/games/{$game->id}");

        $this->assertSame(2, $requests->first()->fresh()->token_version);
        \Mail::assertSent(RatingRequestMail::class, 1);
    }

    public function test_game_detail_exposes_given_ratings_in_the_ratings_overview_shape(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $players = Player::factory()->count(3)->create();
        $game = Game::factory()->completed()->create(['played_at' => now()->addDay()]);
        $game->teams()->attach($players->mapWithKeys(fn ($player, $index) => [
            $player->id => ['team' => $index === 0 ? 'team1' : 'team2'],
        ])->all());

        $game->ratings()->createMany([
            ['rating_player_id' => $players[0]->id, 'rated_player_id' => $players[1]->id, 'rating_value' => 8.5],
            ['rating_player_id' => $players[0]->id, 'rated_player_id' => $players[2]->id, 'rating_value' => 7.5],
        ]);

        $this->get("/games/{$game->id}")->assertInertia(fn ($page) => $page
            ->component('Games/Show')
            ->where('givenRatings.ratingsByPlayer.0.rating_player_id', $players[0]->id)
            ->where('givenRatings.ratingsByPlayer.0.rating_player_name', $players[0]->name)
            ->where('givenRatings.ratingsByPlayer.0.ratings.0.rated_player_id', $players[1]->id)
            ->where('givenRatings.ratingsByPlayer.0.ratings.0.rating_value', 8.5)
            ->has('givenRatings.players', 3)
        );
    }

    public function test_only_the_latest_completed_game_result_can_be_edited(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $olderGame = Game::factory()->completed(1, 0)->create(['played_at' => '2026-08-21 20:00']);
        $latestGame = Game::factory()->completed(2, 1)->create(['played_at' => '2026-08-28 20:00']);

        $this->get("/games/{$latestGame->id}/edit")->assertForbidden();

        $this->put("/games/{$latestGame->id}", [
            'played_at' => '2026-08-29 20:00',
        ])->assertRedirect("/games/{$latestGame->id}")
            ->assertSessionHas('error', 'Een wedstrijd met een resultaat kan niet meer worden bewerkt.');

        $this->assertDatabaseHas('games', [
            'id' => $latestGame->id,
            'played_at' => '2026-08-28 20:00',
        ]);

        $this->get("/games/{$olderGame->id}/enter-result")->assertForbidden();

        $this->post("/games/{$olderGame->id}/results", [
            'team1_score' => 9,
            'team2_score' => 9,
        ])->assertRedirect("/games/{$olderGame->id}")
            ->assertSessionHas('error', 'Alleen het resultaat van de meest recente wedstrijd kan worden bewerkt.');

        $this->assertSame(1, $olderGame->fresh()->team1_score);
        $this->assertSame(0, $olderGame->fresh()->team2_score);

        $this->post("/games/{$latestGame->id}/results", [
            'team1_score' => 4,
            'team2_score' => 3,
        ])->assertRedirect("/games/{$latestGame->id}");

        $this->assertSame(4, $latestGame->fresh()->team1_score);
        $this->assertSame(3, $latestGame->fresh()->team2_score);
    }

    /** @return array{Game, Collection<int, RatingRequest>, Collection<int, Player>} */
    private function gameWithRatingRequests(int $requestCount): array
    {
        $players = Player::factory()->count(10)->create();
        $game = Game::factory()->completed()->create(['played_at' => now()->addDay()]);
        $game->teams()->attach(array_fill_keys($players->take(5)->modelKeys(), ['team' => 'team1']));
        $game->teams()->attach(array_fill_keys($players->skip(5)->modelKeys(), ['team' => 'team2']));

        foreach ($players as $player) {
            $game->gamePlayerRatings()->create([
                'player_id' => $player->id,
                'rating' => 700,
                'type' => $player->type,
            ]);
        }

        $requests = $players->take($requestCount)->map(fn ($player) => RatingRequest::create([
            'game_id' => $game->id,
            'player_id' => $player->id,
            'status' => RatingRequest::STATUS_PENDING,
            'sent_at' => now(),
            'expires_at' => Carbon::now()->addHours(72),
            'token_version' => 1,
        ]));

        $requests->each(fn ($request) => $request->events()->create([
            'new_player_id' => $request->player_id,
            'type' => 'initial_send',
            'expires_at' => $request->expires_at,
        ]));

        return [$game, $requests, $players];
    }
}
