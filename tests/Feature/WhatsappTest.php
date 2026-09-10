<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Player;
use App\Models\User;
use App\Models\WhatsappMessage;
use App\Models\WhatsappSetting;
use App\Services\WhatsappService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class WhatsappTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        $this->actingAs(User::factory()->admin()->create());
    }

    private function fake($callback): void
    {
        Http::swap(new Factory);
        Http::preventStrayRequests();
        Http::fake($callback);
    }

    private function configured(): WhatsappSetting
    {
        $settings = WhatsappSetting::current();
        $settings->update(['token' => 'secret-token', 'enabled' => true, 'group_id' => '12345678901@g.us', 'group_name' => 'Voetbal', 'connection_status' => 'connected', 'phone' => '31612345678']);

        return $settings;
    }

    private function gameRequest(): array
    {
        return [
            'played_at' => '2026-09-11',
            'players' => Player::factory()->count(10)->create()->modelKeys(),
            'send_whatsapp' => true, 'whatsapp_action_key' => (string) Str::uuid(),
        ];
    }

    private function health(): array
    {
        return ['status' => ['code' => 4, 'text' => 'AUTH'], 'user' => ['id' => '31612345678', 'phone' => '31612345678']];
    }

    public function test_admin_page_is_disabled_initially_and_player_cannot_access_any_endpoint(): void
    {
        $this->get('/settings/whatsapp')->assertInertia(fn ($page) => $page
            ->component('Settings/Whatsapp')->where('settings.enabled', false)->where('settings.has_token', false)->missing('settings.token'));
        $this->actingAs(User::factory()->create());
        $this->get('/settings/whatsapp')->assertForbidden();
        $this->putJson('/settings/whatsapp', [])->assertForbidden();
        $this->putJson('/settings/whatsapp/token', [])->assertForbidden();
        $this->postJson('/settings/whatsapp/status')->assertForbidden();
        $this->postJson('/settings/whatsapp/qr')->assertForbidden();
        $this->getJson('/settings/whatsapp/groups')->assertForbidden();
        $this->postJson('/settings/whatsapp/test')->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_token_is_encrypted_never_returned_and_replacement_resets_configuration(): void
    {
        $settings = $this->configured();
        $this->fake(['*/health*' => Http::response($this->health())]);
        $response = $this->putJson('/settings/whatsapp/token', ['token' => 'replacement-secret']);
        $response->assertOk()->assertJsonPath('settings.enabled', false)->assertJsonPath('settings.group_id', null);
        $this->assertStringNotContainsString('replacement-secret', $response->getContent());
        $this->assertStringNotContainsString('replacement-secret', DB::table('whatsapp_settings')->value('token'));
        $this->assertSame('replacement-secret', $settings->fresh()->token);
        $this->assertSame(2, $settings->fresh()->version);
        $this->get('/settings/whatsapp')->assertInertia(fn ($page) => $page->missing('settings.token')->where('settings.has_token', true));
    }

    public function test_invalid_token_does_not_replace_working_settings_or_leak_provider_payload(): void
    {
        $settings = $this->configured();
        $this->fake(['*' => Http::response(['error' => 'private token=bad-token'], 401)]);
        $response = $this->putJson('/settings/whatsapp/token', ['token' => 'bad-token']);
        $response->assertUnprocessable();
        $this->assertStringNotContainsString('bad-token', $response->getContent());
        $this->assertSame('secret-token', $settings->fresh()->token);
        $this->put('/settings/whatsapp/token', ['token' => 'has spaces'])->assertSessionHasErrors('token');
        $this->assertNull(session()->getOldInput('token'));
    }

    public function test_qr_expiry_and_status_are_normalized_without_exposing_provider_details(): void
    {
        $this->configured();
        $png = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j5MsAAAAASUVORK5CYII=';
        $this->fake([
            '*/users/login' => Http::response(['status' => 'OK', 'base64' => $png, 'expire' => 20]),
            '*/health*' => Http::response($this->health() + ['ip' => 'private-provider-info']),
        ]);
        $this->postJson('/settings/whatsapp/qr')->assertOk()->assertJsonPath('expires_in', 20)->assertJsonPath('image', 'data:image/png;base64,'.$png);
        $this->postJson('/settings/whatsapp/status')->assertOk()->assertJsonPath('settings.connection_status', 'connected')->assertJsonMissingPath('ip');
        $this->assertNotNull(WhatsappSetting::current()->checked_at);
        $this->fake(['*' => Http::response(['status' => 'TIMEOUT'])]);
        $this->postJson('/settings/whatsapp/qr')->assertUnprocessable();
        $this->fake(['*' => Http::response(['error' => 'expired token'], 401)]);
        $this->postJson('/settings/whatsapp/status')->assertOk()->assertJsonPath('settings.connection_status', 'error');
        $this->assertSame('error', WhatsappSetting::current()->connection_status);
    }

    public function test_disconnected_channel_cannot_activate_and_new_phone_resets_group(): void
    {
        $this->configured();
        $this->fake(['*' => Http::response(['status' => ['code' => 3, 'text' => 'QR']])]);
        $this->putJson('/settings/whatsapp', ['group_id' => '12345678901@g.us', 'enabled' => true, 'version' => 1])->assertUnprocessable();
        $this->fake(['*' => Http::response(['status' => ['text' => 'AUTH'], 'user' => ['id' => '31699999999']])]);
        $this->postJson('/settings/whatsapp/status')->assertOk()->assertJsonPath('settings.group_id', null)->assertJsonPath('settings.enabled', false);
    }

    public function test_groups_are_paginated_and_only_a_provider_group_can_be_selected(): void
    {
        $this->configured();
        $first = array_map(fn ($i) => ['id' => (12345679000 + $i).'@g.us', 'name' => 'Groep '.$i], range(1, 100));
        $this->fake(function ($request) use ($first) {
            if (str_contains($request->url(), '/health')) {
                return Http::response($this->health());
            }

            return Http::response(['groups' => (int) $request['offset'] === 100 ? [['id' => '99999999999@g.us', 'name' => 'Testgroep']] : $first]);
        });
        $this->getJson('/settings/whatsapp/groups')->assertOk()->assertJsonCount(100, 'groups')->assertJsonPath('next_offset', 100);
        $this->getJson('/settings/whatsapp/groups?offset=100')->assertOk()->assertJsonPath('next_offset', null);
        $this->putJson('/settings/whatsapp', ['group_id' => '99999999999@g.us', 'enabled' => true, 'version' => 1])
            ->assertOk()->assertJsonPath('settings.enabled', false)->assertJsonPath('settings.group_name', 'Testgroep');
        $this->putJson('/settings/whatsapp', ['group_id' => '99999999999@g.us', 'enabled' => true, 'version' => 2])
            ->assertOk()->assertJsonPath('settings.enabled', true);
        $this->putJson('/settings/whatsapp', ['group_id' => '88888888888@g.us', 'enabled' => false, 'version' => 2])->assertUnprocessable();
        $this->putJson('/settings/whatsapp', ['group_id' => '99999999999@g.us', 'enabled' => false, 'version' => 1])->assertUnprocessable();
    }

    public function test_disabling_works_without_provider_and_test_message_is_explicit_and_idempotent(): void
    {
        $this->configured();
        $this->putJson('/settings/whatsapp', ['group_id' => '12345678901@g.us', 'enabled' => false, 'version' => 1])->assertOk();
        Http::assertNothingSent();
        $this->fake(['*/messages/text' => Http::response(['sent' => true, 'message' => ['id' => 'test-id']])]);
        $request = ['action_key' => (string) Str::uuid()];
        $this->postJson('/settings/whatsapp/test', $request)->assertOk()->assertJsonPath('message.status', 'accepted');
        $this->postJson('/settings/whatsapp/test', $request)->assertOk();
        Http::assertSentCount(1);
    }

    public function test_result_submission_can_send_the_rating_request_recipients_to_whatsapp(): void
    {
        \Mail::fake();
        $this->configured();
        $this->fake(['*/messages/text' => Http::response(['sent' => true, 'message' => ['id' => 'ratings-id']])]);
        $players = Player::factory()->count(10)->create(['rating' => 700]);
        $game = Game::factory()->completed()->create(['played_at' => '2026-09-11']);
        $game->teams()->attach(array_fill_keys($players->take(5)->modelKeys(), ['team' => 'team1']));
        $game->teams()->attach(array_fill_keys($players->skip(5)->modelKeys(), ['team' => 'team2']));

        foreach ($players as $player) {
            $game->gamePlayerRatings()->create(['player_id' => $player->id, 'rating' => 700, 'type' => $player->type]);
        }

        $this->post("/games/{$game->id}/results", [
            'team1_score' => 4,
            'team2_score' => 1,
            'send_rating_requests' => true,
            'send_whatsapp' => true,
            'whatsapp_action_key' => (string) Str::uuid(),
        ])->assertRedirect("/games/{$game->id}");

        $message = WhatsappMessage::firstOrFail();
        $this->assertSame('rating_requests', $message->kind);
        $this->assertSame('accepted', $message->status);
        $this->assertStringStartsWith('Beoordelingsaanvragen — 11-09-2026', $message->body);
        foreach ($game->ratingRequests()->with('player.user')->get() as $ratingRequest) {
            $this->assertStringContainsString($ratingRequest->player->name.' ('.$ratingRequest->player->user->email.')', $message->body);
        }
        $this->assertStringNotContainsString('700', $message->body);
    }

    public function test_lineup_uses_snapshot_totals_roles_names_and_order_without_individual_ratings(): void
    {
        $game = Game::factory()->create(['played_at' => '2026-09-11']);
        foreach ([['Zoe', 'team1', 'both', 600], ['Auke', 'team1', 'defender', 700], ['Jan', 'team2', 'attacker', 800]] as [$name, $team, $type, $rating]) {
            $player = Player::factory()->create(['name' => $name, 'type' => 'attacker', 'rating' => 999]);
            $game->teams()->attach($player, ['team' => $team]);
            $game->gamePlayerRatings()->create(['player_id' => $player->id, 'rating' => $rating, 'type' => $type]);
        }
        $this->assertSame("Teamindeling — 11-09-2026\n\nSpelers in team 1 (1300)\n(Verdediger) Auke\n(Beide) Zoe\n\nSpelers in team 2 (800)\n(Aanvaller) Jan", app(WhatsappService::class)->body($game, 'create'));
    }

    public function test_game_is_saved_before_send_and_duplicate_submission_does_not_repeat_it(): void
    {
        $this->configured();
        $this->fake(function ($request) {
            $this->assertDatabaseCount('game_player_ratings', 10);
            $this->assertDatabaseCount('teams', 10);

            return Http::response(['sent' => true, 'message' => ['id' => 'sent-id']]);
        });
        $data = $this->gameRequest();
        $this->post('/games', $data)->assertRedirect();
        $this->post('/games', $data)->assertRedirect();
        $this->assertDatabaseCount('games', 1);
        $this->assertDatabaseCount('whatsapp_messages', 1);
        Http::assertSentCount(1);
        $this->assertSame('accepted', WhatsappMessage::first()->status);
        $this->get('/games/'.Game::first()->id)->assertInertia(fn ($page) => $page->where('whatsappMessage.status', 'accepted')->where('whatsappMessage.can_retry', false));
        $data['played_at'] = '2026-09-12';
        $this->post('/games', $data)->assertSessionHasErrors('whatsapp');
        Http::assertSentCount(1);
    }

    public function test_unchecked_missing_and_invalid_checkbox_requests_never_send(): void
    {
        $this->configured();
        $this->get('/games/create')->assertInertia(fn ($page) => $page->where('whatsappReady', true));
        $data = $this->gameRequest();
        $data['send_whatsapp'] = false;
        $this->post('/games', $data)->assertRedirect();
        unset($data['send_whatsapp']);
        $this->post('/games', $data)->assertRedirect();
        $data['send_whatsapp'] = 'invalid';
        $this->post('/games', $data)->assertSessionHasErrors('send_whatsapp');
        $data['send_whatsapp'] = true;
        unset($data['whatsapp_action_key']);
        $this->post('/games', $data)->assertSessionHasErrors('whatsapp_action_key');
        $data['whatsapp_action_key'] = (string) Str::uuid();
        $data['players'] = [];
        $this->post('/games', $data)->assertSessionHasErrors('players');
        $this->assertDatabaseCount('whatsapp_messages', 0);
        Http::assertNothingSent();
    }

    public function test_edit_only_sends_when_requested_and_adds_changed_heading(): void
    {
        $this->configured();
        $this->fake(['*' => Http::response(['sent' => true])]);
        $data = $this->gameRequest();
        $data['send_whatsapp'] = false;
        $this->post('/games', $data)->assertRedirect();
        $game = Game::first();
        $this->get('/games/'.$game->id.'/edit')->assertInertia(fn ($page) => $page->where('mode', 'edit')->where('whatsappReady', true));
        $this->put('/games/'.$game->id, $data)->assertRedirect();
        Http::assertNothingSent();
        $data['send_whatsapp'] = true;
        $this->put('/games/'.$game->id, $data)->assertRedirect();
        $this->put('/games/'.$game->id, $data)->assertRedirect();
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => str_starts_with($request['body'], 'Gewijzigde indeling'));
    }

    public function test_provider_rejections_leave_game_intact_and_use_safe_error_codes(): void
    {
        $this->configured();
        foreach ([401 => 'authorization', 402 => 'limit', 403 => 'forbidden', 429 => 'rate_limit', 500 => 'provider'] as $status => $reason) {
            $this->fake(['*' => Http::response(['error' => 'secret provider payload'], $status)]);
            $data = $this->gameRequest();
            $this->post('/games', $data)->assertRedirect();
            $message = WhatsappMessage::latest('id')->first();
            $this->assertSame($reason, $message->error_code);
            $this->assertSame($status === 500 ? 'uncertain' : 'failed', $message->status);
            $this->assertCount(10, Game::find($message->game_id)->teams);
        }
    }

    public function test_timeout_requires_group_check_and_retry_is_idempotent(): void
    {
        $this->configured();
        $this->fake(fn () => throw new ConnectionException('secret transport details'));
        $data = $this->gameRequest();
        $this->post('/games', $data)->assertRedirect();
        $message = WhatsappMessage::first();
        $this->assertSame('uncertain', $message->status);
        $url = "/games/{$message->game_id}/whatsapp/{$message->id}/retry";
        $retry = ['action_key' => (string) Str::uuid()];
        $this->post($url, $retry)->assertSessionHasErrors('whatsapp');
        $this->fake(['*' => Http::response(['sent' => true])]);
        $retry['checked_group'] = true;
        $this->post($url, $retry)->assertRedirect();
        $this->post($url, $retry)->assertRedirect();
        Http::assertSentCount(1);
        $this->assertDatabaseCount('whatsapp_messages', 2);
        $this->assertSame('accepted', WhatsappMessage::latest('id')->first()->status);
        $this->actingAs(User::factory()->create());
        $this->post($url, $retry)->assertForbidden();
    }

    public function test_retry_rejects_outdated_lineup_and_changed_configuration(): void
    {
        $settings = $this->configured();
        $this->fake(['*' => Http::response([], 403)]);
        $this->post('/games', $this->gameRequest())->assertRedirect();
        $message = WhatsappMessage::first();
        $url = "/games/{$message->game_id}/whatsapp/{$message->id}/retry";
        $game = Game::find($message->game_id);
        $game->update(['played_at' => '2026-09-12']);
        $this->post($url, ['action_key' => (string) Str::uuid()])->assertSessionHasErrors('whatsapp');
        $game->update(['played_at' => '2026-09-11']);
        $settings->update(['version' => 2]);
        $this->post($url, ['action_key' => (string) Str::uuid()])->assertSessionHasErrors('whatsapp');
        Http::assertSentCount(1);
    }

    public function test_interrupted_send_is_uncertain_and_cannot_be_automatically_dispatched_again(): void
    {
        $this->configured();
        $this->fake(['*' => Http::response([], 403)]);
        $this->post('/games', $this->gameRequest())->assertRedirect();
        $message = WhatsappMessage::first();
        $message->update(['status' => 'sending', 'attempted_at' => now()->subMinute()]);
        $this->assertSame('uncertain', $message->displayStatus());
        app(WhatsappService::class)->dispatch($message);
        Http::assertSentCount(1);
    }

    public function test_disabled_configuration_preserves_game_without_network(): void
    {
        $this->post('/games', $this->gameRequest())->assertRedirect();
        $this->assertDatabaseCount('games', 1);
        $this->assertSame('not_configured', WhatsappMessage::first()->error_code);
        Http::assertNothingSent();
    }
}
