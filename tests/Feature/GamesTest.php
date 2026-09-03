<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Player;
use App\Models\User;
use App\Mail\RatingRequestMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GamesTest extends TestCase
{
    use RefreshDatabase;

    public function test_game_creation_persists_snapshots_and_balanced_teams(): void
    {
        $this->actingAs(User::factory()->create());
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
        $this->actingAs(User::factory()->create());
        $players = Player::factory()->count(10)->create(['rating' => 700]);
        $game = Game::factory()->completed()->create();
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
        $this->assertSame('Vrijdag voetbal', $mail->from[0]['name']);
    }

    public function test_game_detail_exposes_given_ratings_in_the_ratings_overview_shape(): void
    {
        $this->actingAs(User::factory()->create());
        $players = Player::factory()->count(3)->create();
        $game = Game::factory()->completed()->create();
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
        $this->actingAs(User::factory()->create());
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
}
