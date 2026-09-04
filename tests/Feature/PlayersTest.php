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
        $this->actingAs(User::factory()->admin()->create());

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
        $this->actingAs(User::factory()->admin()->create());

        $this->post('/players', [
            'players' => [
                ['name' => 'Solo player', 'email' => 'solo@example.test', 'rating' => 8, 'type' => 'both'],
            ],
        ])->assertRedirect('/players')->assertSessionHas('success', 'Speler succesvol aangemaakt.');
    }

    public function test_updating_a_player_uses_the_dutch_success_message(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $player = Player::factory()->create();

        $this->put("/players/{$player->id}", [
            'name' => 'Updated player',
            'username' => 'updated-login',
            'email' => 'updated@example.test',
            'rating' => 8.5,
            'type' => 'both',
            'role' => User::ROLE_PLAYER,
        ])->assertRedirect('/players')->assertSessionHas('success', 'Speler succesvol bijgewerkt.');

        $this->assertDatabaseHas('players', ['id' => $player->id, 'name' => 'Updated player']);
        $this->assertDatabaseHas('users', ['id' => $player->user_id, 'name' => 'updated-login']);
    }

    public function test_player_list_is_served_as_the_expected_inertia_page(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->get('/players')->assertInertia(fn ($page) => $page
            ->component('Players/Index')
            ->has('players')
            ->where('sortBy', 'name')
            ->where('translations.Admin', 'Beheerder')
            ->where('translations.Player', 'Speler')
        );
    }

    public function test_player_validation_preserves_the_players_array_contract(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->from('/players/create')->post('/players', [
            'players' => [['name' => '', 'email' => 'not-an-email', 'rating' => 11, 'type' => 'invalid']],
        ])->assertRedirect('/players/create')->assertSessionHasErrors([
            'players.0.name', 'players.0.email', 'players.0.rating', 'players.0.type',
        ]);
    }

    public function test_delete_and_restore_keep_the_player_and_user_relationship(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $player = Player::factory()->create();

        $this->delete("/players/{$player->id}")->assertRedirect('/players');
        $this->assertSoftDeleted('players', ['id' => $player->id]);
        $this->assertSoftDeleted('users', ['id' => $player->user_id]);

        $this->patch("/players/{$player->id}/restore")->assertRedirect('/players');
        $this->assertDatabaseHas('players', ['id' => $player->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('users', ['id' => $player->user_id, 'deleted_at' => null]);
    }

    public function test_new_players_are_created_with_the_player_role(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->post('/players', [
            'players' => [[
                'name' => 'New player',
                'email' => 'new-player@example.test',
                'rating' => 8,
                'type' => 'both',
            ]],
        ])->assertRedirect('/players');

        $this->assertDatabaseHas('users', [
            'email' => 'new-player@example.test',
            'role' => User::ROLE_PLAYER,
        ]);
    }

    public function test_new_players_can_reuse_an_existing_email_address(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        User::factory()->create(['email' => 'shared@example.test']);

        $this->post('/players', [
            'players' => [[
                'name' => 'Another player',
                'email' => 'shared@example.test',
                'rating' => 8,
                'type' => 'both',
            ]],
        ])->assertRedirect('/players');

        $this->assertSame(2, User::where('email', 'shared@example.test')->count());
    }

    public function test_admin_can_update_a_players_role_from_the_existing_edit_flow(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $player = Player::factory()->create();

        $this->put("/players/{$player->id}", [
            'name' => $player->name,
            'username' => $player->user->name,
            'email' => $player->user->email,
            'rating' => $player->rating / 100,
            'type' => $player->type,
            'role' => User::ROLE_ADMIN,
        ])->assertRedirect('/players');

        $this->assertSame(User::ROLE_ADMIN, $player->user->fresh()->role);
    }

    public function test_duplicate_usernames_are_rejected_but_duplicate_emails_are_allowed_on_update(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $otherUser = User::factory()->create([
            'name' => 'Existing username',
            'email' => 'shared@example.test',
        ]);
        $player = Player::factory()->create();

        $this->put("/players/{$player->id}", [
            'name' => $player->name,
            'username' => $otherUser->name,
            'email' => $otherUser->email,
            'rating' => $player->rating / 100,
            'type' => $player->type,
            'role' => User::ROLE_PLAYER,
        ])->assertSessionHasErrors('username');

        $this->put("/players/{$player->id}", [
            'name' => $player->name,
            'username' => 'Unique username',
            'email' => $otherUser->email,
            'rating' => $player->rating / 100,
            'type' => $player->type,
            'role' => User::ROLE_PLAYER,
        ])->assertRedirect('/players');

        $this->assertDatabaseHas('users', [
            'id' => $player->user_id,
            'name' => 'Unique username',
            'email' => 'shared@example.test',
        ]);
    }

    public function test_players_cannot_access_the_admin_application(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/dashboard')->assertForbidden();
        $this->get('/players')->assertForbidden();
        $this->get('/games')->assertForbidden();
    }

    public function test_the_last_admin_cannot_be_demoted_or_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $player = Player::factory()->create(['user_id' => $admin->id]);
        $this->actingAs($admin);

        $this->put("/players/{$player->id}", [
            'name' => $player->name,
            'username' => $admin->name,
            'email' => $admin->email,
            'rating' => $player->rating / 100,
            'type' => $player->type,
            'role' => User::ROLE_PLAYER,
        ])->assertSessionHasErrors('role');

        $this->delete("/players/{$player->id}")
            ->assertRedirect('/players')
            ->assertSessionHas('error', 'Je kunt je eigen spelersaccount niet verwijderen.');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => User::ROLE_ADMIN]);
    }

    public function test_player_list_includes_roles_for_active_and_soft_deleted_players(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $admin = User::factory()->admin()->create();
        $adminPlayer = Player::factory()->create(['user_id' => $admin->id]);
        $deletedPlayer = Player::factory()->create();
        $deletedPlayer->delete();

        $this->get('/players?include_deleted=1')->assertInertia(fn ($page) => $page
            ->component('Players/Index')
            ->where('players', fn ($players): bool => collect($players)->contains(fn ($item) => $item['id'] === $adminPlayer->id && $item['user']['role'] === User::ROLE_ADMIN))
            ->where('players', fn ($players): bool => collect($players)->contains(fn ($item) => $item['id'] === $deletedPlayer->id && $item['user']['role'] === User::ROLE_PLAYER))
        );
    }

    public function test_protected_admin_cannot_be_demoted_or_deleted(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Sjoerd Koffeman',
            'email' => 'skoffeman@live.nl',
        ]);
        $player = Player::factory()->create(['user_id' => $admin->id]);
        $this->actingAs(User::factory()->admin()->create());

        $this->put("/players/{$player->id}", [
            'name' => $player->name,
            'username' => $admin->name,
            'email' => $admin->email,
            'rating' => $player->rating / 100,
            'type' => $player->type,
            'role' => User::ROLE_PLAYER,
        ])->assertSessionHasErrors('role');

        $this->delete("/players/{$player->id}")
            ->assertRedirect('/players')
            ->assertSessionHas('error', 'Dit beheerdersaccount is beschermd.');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => User::ROLE_ADMIN]);
        $this->assertDatabaseHas('players', ['id' => $player->id, 'deleted_at' => null]);
    }

    public function test_duplicate_email_with_a_different_name_is_not_a_protected_admin(): void
    {
        $protectedAdmin = User::factory()->admin()->create([
            'name' => 'Sjoerd Koffeman',
            'email' => 'skoffeman@live.nl',
        ]);
        $otherUser = User::factory()->create([
            'name' => 'Other player',
            'email' => 'skoffeman@live.nl',
        ]);

        $this->assertTrue($protectedAdmin->isProtectedAdmin());
        $this->assertFalse($otherUser->isProtectedAdmin());
    }
}
