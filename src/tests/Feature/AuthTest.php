<?php

namespace Feature;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    public function test_user_can_register(): void
    {
        $response = $this->postJson('api/register',[
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['user','token']);
    }

    public function test_forgot_password_sends_reset_link_for_valid_email()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/forgot-password', ['email' => $user->email]);
        $response->assertStatus(200)
            ->assertJson(['message' => 'Password reset link sent']);
    }

    public function test_forgot_password_fails_for_invalid_email()
    {
        $response = $this->postJson('/api/forgot-password', ['email' => 'nonexistent@example.com']);

        $response->assertStatus(400)
            ->assertJson(['message' => 'Unable to send reset link']);
    }

    public function test_forgot_password_requires_email()
    {
        $response = $this->postJson('/api/forgot-password', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_reset_password_successfully_resets_password()
    {
        Event::fake();

        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->postJson('/api/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Password has been reset']);

        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password));

        Event::assertDispatched(PasswordReset::class);
    }

    public function test_reset_password_fails_with_invalid_token()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/reset-password', [
            'email' => $user->email,
            'token' => 'invalid-token',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        $response->assertStatus(400)
            ->assertJson(['message' => 'Invalid token or email']);
    }

    public function test_reset_password_requires_all_fields()
    {
        $response = $this->postJson('/api/reset-password', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'token', 'password']);
    }

    public function test_reset_password_requires_password_confirmation()
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->postJson('/api/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'newpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }


}
