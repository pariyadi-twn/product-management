<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_page(): void
    {
        $response = $this->get(route('products.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('Kata Sandi');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('products.index'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => 'admin@example.com',
            'password' => 'salah',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_login_is_throttled_after_too_many_attempts(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.store'), [
                'email' => 'admin@example.com',
                'password' => 'salah',
            ]);
        }

        $response = $this->post(route('login.store'), [
            'email' => 'admin@example.com',
            'password' => 'salah',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'Terlalu banyak percobaan',
            session('errors')->first('email'),
        );
    }
}
