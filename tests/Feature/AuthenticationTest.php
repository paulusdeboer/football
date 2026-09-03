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
        $user = User::factory()->create(['name' => 'Test player', 'password' => bcrypt('secret')]);

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
        User::factory()->create(['name' => 'Test player', 'password' => bcrypt('secret')]);

        $this->from('/login')->post('/login', [
            'name' => 'Test player',
            'password' => 'wrong',
        ])->assertRedirect('/login')->assertSessionHasErrors('name');
    }

    public function test_user_can_register_with_the_existing_payload_and_redirect(): void
    {
        $this->post('/register', [
            'name' => 'New player',
            'email' => 'new-player@example.test',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['name' => 'New player', 'email' => 'new-player@example.test']);
    }

    public function test_user_can_reset_a_password_through_the_explicit_reset_routes(): void
    {
        $user = User::factory()->create(['email' => 'reset@example.test', 'password' => 'old-password']);
        $token = Password::broker()->createToken($user);

        $this->post('/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect('/login');

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
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
        $this->assertSame('Vrijdag voetbal', $message->from[1]);
    }

    public function test_protected_pages_redirect_guests_to_login(): void
    {
        $this->get('/players')->assertRedirect('/login');
    }

    public function test_user_can_log_out(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }
}
