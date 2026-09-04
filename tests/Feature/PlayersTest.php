<?php

namespace Tests\Feature;

use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlayersTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_multiple_players_with_integer_scale_rating(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post('/players', [
            'players' => [
                ['name' => 'Attacker', 'email' => 'attacker@example.test', 'rating' => 8.5, 'type' => 'attacker'],
                ['name' => 'Defender', 'email' => 'defender@example.test', 'rating' => 7, 'type' => 'defender'],
            ],
        ]);

        $response->assertRedirect('/players')->assertSessionHas('success', '2 spelers succesvol aangemaakt.');
        $this->assertDatabaseHas('players', ['name' => 'Attacker', 'rating' => 850, 'type' => 'attacker']);
        $this->assertDatabaseHas('users', ['email' => 'defender@example.test']);
    }

    public function test_creating_one_player_uses_the_singular_success_message(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post('/players', [
            'players' => [
                ['name' => 'Solo player', 'email' => 'solo@example.test', 'rating' => 8, 'type' => 'both'],
            ],
        ])->assertRedirect('/players')->assertSessionHas('success', 'Speler succesvol aangemaakt.');
    }

    public function test_updating_a_player_uses_the_dutch_success_message(): void
    {
        $this->actingAs(User::factory()->create());
        $player = Player::factory()->create();

        $this->put("/players/{$player->id}", [
            'name' => 'Updated player',
            'email' => 'updated@example.test',
            'rating' => 8.5,
            'type' => 'both',
        ])->assertRedirect('/players')->assertSessionHas('success', 'Speler succesvol bijgewerkt.');
    }

    public function test_player_list_is_served_as_the_expected_inertia_page(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/players')->assertInertia(fn ($page) => $page
            ->component('Players/Index')
            ->has('players')
            ->where('sortBy', 'name')
        );
    }

    public function test_player_validation_preserves_the_players_array_contract(): void
    {
        $this->actingAs(User::factory()->create());

        $this->from('/players/create')->post('/players', [
            'players' => [['name' => '', 'email' => 'not-an-email', 'rating' => 11, 'type' => 'invalid']],
        ])->assertRedirect('/players/create')->assertSessionHasErrors([
            'players.0.name', 'players.0.email', 'players.0.rating', 'players.0.type',
        ]);
    }

    public function test_delete_and_restore_keep_the_player_and_user_relationship(): void
    {
        $this->actingAs(User::factory()->create());
        $player = Player::factory()->create();

        $this->delete("/players/{$player->id}")->assertRedirect('/players');
        $this->assertSoftDeleted('players', ['id' => $player->id]);
        $this->assertSoftDeleted('users', ['id' => $player->user_id]);

        $this->patch("/players/{$player->id}/restore")->assertRedirect('/players');
        $this->assertDatabaseHas('players', ['id' => $player->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('users', ['id' => $player->user_id, 'deleted_at' => null]);
    }
}
