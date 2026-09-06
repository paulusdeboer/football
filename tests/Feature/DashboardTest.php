<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GamePlayerRating;
use App\Models\Player;
use App\Models\Rating;
use App\Models\RatingRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admins_can_view_the_dashboard(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('dashboard'))->assertForbidden();
    }

    public function test_dashboard_contains_aggregated_statistics_for_completed_games(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        [$first, $second, $third] = Player::factory()->count(3)->create(['rating' => 800])->all();
        $game = $this->createGame([$first, $second, $third], now()->subDay(), 4, 2);

        Rating::create([
            'game_id' => $game->id,
            'rated_player_id' => $first->id,
            'rating_player_id' => $second->id,
            'rating_value' => 8.5,
        ]);
        Rating::create([
            'game_id' => $game->id,
            'rated_player_id' => $first->id,
            'rating_player_id' => $third->id,
            'rating_value' => 7.5,
        ]);
        RatingRequest::create([
            'game_id' => $game->id,
            'player_id' => $second->id,
            'status' => RatingRequest::STATUS_COMPLETED,
            'sent_at' => now()->subHours(2),
            'completed_at' => now()->subHour(),
            'expires_at' => now()->addDay(),
            'token_version' => 1,
        ]);

        $this->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard')
                ->where('filters.period', '10')
                ->where('summary.active_players', 3)
                ->where('summary.headline_rating', 8)
                ->where('summary.headline_rating_label', 'Average current rating')
                ->where('summary.games_played', 1)
                ->where('summary.average_received_rating', 8)
                ->where('summary.open_rating_requests', 0)
                ->where('charts.ratingRequests.counts.completed', 1)
                ->where('ranking', fn ($ranking): bool => ($player = $ranking->firstWhere('id', $first->id))
                    && $player['average_rating'] === 8
                    && $player['ratings_count'] === 2)
                ->has('recentGames', 1)
            );
    }

    public function test_dashboard_period_excludes_unfinished_games_and_limits_completed_games(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $players = Player::factory()->count(2)->create();

        for ($index = 0; $index < 7; $index++) {
            $this->createGame($players->all(), now()->subDays($index), 1, 0);
        }
        Game::factory()->create(['played_at' => now()->addDay()]);

        $this->get(route('dashboard', ['period' => '5']))
            ->assertInertia(fn ($page) => $page
                ->where('filters.period', '5')
                ->where('summary.games_played', 5)
                ->has('charts.ratingTrend.labels', 5)
                ->has('recentGames', 5)
            );

        $this->get(route('dashboard', ['period' => '1']))
            ->assertInertia(fn ($page) => $page
                ->where('filters.period', '1')
                ->where('summary.games_played', 1)
                ->has('charts.ratingTrend.labels', 1)
                ->has('recentGames', 1)
            );
    }

    public function test_player_filter_reduces_rating_trend_to_the_selected_player(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        [$first, $second] = Player::factory()->count(2)->create();
        $game = $this->createGame([$first, $second], now()->subDay(), 2, 2);
        Rating::create([
            'game_id' => $game->id,
            'rated_player_id' => $second->id,
            'rating_player_id' => $first->id,
            'rating_value' => 8,
        ]);
        RatingRequest::create([
            'game_id' => $game->id,
            'player_id' => $second->id,
            'status' => RatingRequest::STATUS_PENDING,
            'sent_at' => now()->subHour(),
            'expires_at' => now()->addDay(),
            'token_version' => 1,
        ]);

        $this->get(route('dashboard', ['player_id' => $second->id]))
            ->assertInertia(fn ($page) => $page
                ->where('filters.player_ids', [$second->id])
                ->where('summary.games_played', 1)
                ->where('summary.average_received_rating', 8)
                ->where('summary.open_rating_requests', 1)
                ->where('summary.selected_player_name', $second->name)
                ->where('summary.headline_rating', $second->rating / 100)
                ->where('summary.headline_rating_label', 'Current rating')
                ->has('charts.ratingTrend.datasets', 1)
                ->where('charts.ratingTrend.datasets.0.label', $second->name)
                ->where('charts.playerResults.datasets.0.data', [0, 1, 0])
                ->where('charts.averageRatings.labels', fn ($labels): bool => $labels->all() === [$second->name])
            );
    }

    public function test_multiple_player_filter_updates_player_specific_statistics(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        [$first, $second] = Player::factory()->count(2)->create(['rating' => 800]);
        $this->createGame([$first, $second], now()->subDay(), 2, 1);
        $this->createGame([$first], now()->subDays(2), 1, 0);

        $this->get(route('dashboard', ['player_ids' => [$first->id, $second->id]]))
            ->assertInertia(fn ($page) => $page
                ->where('filters.player_ids', [$first->id, $second->id])
                ->where('summary.headline_rating_label', 'Average selected rating')
                ->where('summary.games_played', 1.5)
                ->where('summary.games_played_label', 'Average games played')
                ->where('summary.selected_player_count', 2)
                ->has('charts.ratingTrend.datasets', 2)
                ->where('charts.ratingTrend.datasets', fn ($datasets): bool => $datasets->pluck('borderColor')->unique()->count() === 2)
                ->has('charts.playerResults.datasets', 2)
                ->where('charts.playerResults.datasets', fn ($datasets): bool => $datasets->pluck('backgroundColor')->unique()->count() === 2)
            );
    }

    public function test_default_rating_trend_shows_fifteen_most_played_players(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $players = Player::factory()->count(16)->create();
        $this->createGame($players->all(), now()->subDays(2), 2, 1);
        $this->createGame($players->take(15)->all(), now()->subDay(), 2, 1);

        $this->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page
                ->has('charts.ratingTrend.datasets', 15)
            );
    }

    public function test_default_average_ratings_shows_twenty_most_played_players(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $players = Player::factory()->count(21)->create(['rating' => 800]);
        $firstGame = $this->createGame($players->all(), now()->subDays(2), 2, 1);
        $this->createGame($players->take(20)->all(), now()->subDay(), 2, 1);

        foreach ($players as $player) {
            Rating::create([
                'game_id' => $firstGame->id,
                'rated_player_id' => $player->id,
                'rating_player_id' => $player->id === $players->first()->id ? $players[1]->id : $players->first()->id,
                'rating_value' => 8,
            ]);
        }

        $this->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page
                ->has('charts.averageRatings.labels', 20)
                ->where('charts.averageRatings.labels', fn ($labels): bool => ! $labels->contains($players->last()->name))
            );
    }

    public function test_twenty_selected_players_receive_unique_chart_colors(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $players = Player::factory()->count(20)->create(['rating' => 800]);
        $this->createGame($players->all(), now()->subDay(), 2, 1);

        $this->get(route('dashboard', ['player_ids' => $players->modelKeys()]))
            ->assertInertia(fn ($page) => $page
                ->has('charts.ratingTrend.datasets', 20)
                ->where('charts.ratingTrend.datasets', fn ($datasets): bool => $datasets->pluck('borderColor')->unique()->count() === 20)
                ->has('charts.playerResults.datasets', 20)
                ->where('charts.playerResults.datasets', fn ($datasets): bool => $datasets->pluck('backgroundColor')->unique()->count() === 20)
            );
    }

    public function test_player_filter_options_are_sorted_alphabetically(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        Player::factory()->create(['name' => 'Zulu', 'rating' => 900]);
        Player::factory()->create(['name' => 'Alice', 'rating' => 700]);

        $this->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page
                ->where('players.0.name', 'Alice')
                ->where('players.1.name', 'Zulu')
            );
    }

    public function test_deleted_players_are_not_in_current_ranking_but_remain_in_historical_game_data(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        [$active, $deleted] = Player::factory()->count(2)->create();
        $game = $this->createGame([$active, $deleted], now()->subDay(), 3, 1);
        Rating::create([
            'game_id' => $game->id,
            'rated_player_id' => $deleted->id,
            'rating_player_id' => $active->id,
            'rating_value' => 8,
        ]);
        $deleted->delete();

        $this->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page
                ->where('summary.active_players', 1)
                ->has('ranking', 1)
                ->where('recentGames.0.players_count', 2)
                ->where('charts.ratingTrend.datasets.0.label', $active->name)
                ->where('charts.averageRatings.labels', fn ($labels): bool => $labels->contains($deleted->name))
            );
    }

    /** @param array<int, Player> $players */
    private function createGame(array $players, $playedAt, int $team1Score, int $team2Score): Game
    {
        $game = Game::factory()->completed($team1Score, $team2Score)->create(['played_at' => $playedAt]);
        $half = (int) ceil(count($players) / 2);

        foreach ($players as $index => $player) {
            $team = $index < $half ? 'team1' : 'team2';
            $game->teams()->attach($player->id, ['team' => $team]);
            GamePlayerRating::create([
                'game_id' => $game->id,
                'player_id' => $player->id,
                'rating' => $player->rating,
                'type' => $player->type,
            ]);
        }

        return $game;
    }
}
