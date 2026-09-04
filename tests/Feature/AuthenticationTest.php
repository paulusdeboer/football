<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\PasswordResetNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_available_without_auth_routes_macro(): void
    {
        $this->get('/login')->assertOk()->assertSee('Login');
    }

    public function test_user_can_log_in_with_the_existing_username_payload(): void
    {
        $user = User::factory()->admin()->create(['name' => 'Test player', 'password' => bcrypt('secret')]);

        $response = $this->post('/login', [
            'name' => 'Test player',
            'password' => 'secret',
            'remember' => '1',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_login_returns_validation_errors(): void
    {
        User::factory()->admin()->create(['name' => 'Test player', 'password' => bcrypt('secret')]);

        $this->from('/login')->post('/login', [
            'name' => 'Test player',
            'password' => 'wrong',
        ])->assertRedirect('/login')->assertSessionHasErrors('name');
    }

    public function test_public_registration_is_closed(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register')->assertNotFound();
    }

    public function test_user_can_reset_a_password_through_the_explicit_reset_routes(): void
    {
        $user = User::factory()->create(['email' => 'reset@example.test', 'password' => 'old-password']);
        $token = Password::broker()->createToken($user);

        $this->post('/password/reset', [
            'token' => $token,
            'user_id' => $user->id,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect('/login');

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_password_reset_targets_the_selected_user_when_emails_are_shared(): void
    {
        $target = User::factory()->create([
            'name' => 'Manoach Bolks',
            'email' => 'shared@example.test',
            'password' => 'target-old-password',
        ]);
        $other = User::factory()->create([
            'name' => 'Emiel Bolks',
            'email' => 'shared@example.test',
            'password' => 'other-old-password',
        ]);
        $token = Password::broker()->createToken($target);

        $this->post('/password/reset', [
            'token' => $token,
            'user_id' => $target->id,
            'password' => 'target-new-password',
            'password_confirmation' => 'target-new-password',
        ])->assertRedirect('/login');

        $this->assertTrue(Hash::check('target-new-password', $target->fresh()->password));
        $this->assertTrue(Hash::check('other-old-password', $other->fresh()->password));
    }

    public function test_password_reset_token_cannot_be_used_for_another_user_with_the_same_email(): void
    {
        $target = User::factory()->create(['email' => 'shared@example.test']);
        $other = User::factory()->create(['email' => 'shared@example.test']);
        $targetPassword = $target->password;
        $otherPassword = $other->password;
        $token = Password::broker()->createToken($target);

        $this->from('/password/reset')->post('/password/reset', [
            'token' => $token,
            'user_id' => $other->id,
            'password' => 'should-not-be-applied',
            'password_confirmation' => 'should-not-be-applied',
        ])->assertRedirect('/password/reset')->assertSessionHasErrors('user_id');

        $this->assertSame($targetPassword, $target->fresh()->password);
        $this->assertSame($otherPassword, $other->fresh()->password);
    }

    public function test_password_reset_request_does_not_inject_html_into_the_status_message(): void
    {
        Mail::fake();
        $user = User::factory()->create(['name' => 'Paulus de Boer']);

        $this->from('/password/reset')->post('/password/email', ['name' => $user->name])->assertRedirect('/password/reset');

        $status = session('status');
        $this->assertIsString($status);
        $this->assertStringContainsString($user->name, $status);
        $this->assertStringNotContainsString('<strong>', $status);
        $this->assertStringNotContainsString('<em>', $status);
    }

    public function test_password_reset_notification_uses_the_branded_mail_template(): void
    {
        $user = User::factory()->create(['name' => 'Paulus de Boer']);
        $message = (new PasswordResetNotification('test-token'))->toMail($user);
        $html = (string) $message->render();

        $this->assertStringContainsString('Vrijdag voetbal', $html);
        $this->assertStringContainsString('Wachtwoord resetten', $html);
        $this->assertStringContainsString('favicon.svg', $html);
        $this->assertStringNotContainsString('Laravel', $html);
        $this->assertSame('noreply@vrijdagvoetbal.nl', $message->from[0]);
        $this->assertSame('Vrijdag voetbal', $message->from[1]);
    }

    public function test_protected_pages_redirect_guests_to_login(): void
    {
        $this->get('/players')->assertRedirect('/login');
    }

    public function test_user_can_log_out(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_player_cannot_log_in_to_the_normal_application(): void
    {
        User::factory()->create(['name' => 'Test player', 'password' => bcrypt('secret')]);

        $this->from('/login')->post('/login', [
            'name' => 'Test player',
            'password' => 'secret',
        ])->assertRedirect('/login')->assertSessionHasErrors([
            'name' => 'Alleen beheerders kunnen inloggen.',
        ]);

        $this->assertGuest();
    }
}
