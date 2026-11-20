<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdleTimeoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_is_ended_after_idle_timeout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['auth.last_activity_at' => now()->subMinutes(31)->getTimestamp()])
            ->get(route('products.index'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', 'Sesi Anda telah berakhir karena tidak aktif. Silakan masuk kembali.');
        $this->assertGuest();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => ActivityLog::ACTION_LOGOUT,
        ]);
    }

    public function test_active_session_is_not_ended(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['auth.last_activity_at' => now()->subMinutes(5)->getTimestamp()])
            ->get(route('products.index'));

        $response->assertOk();
        $this->assertAuthenticatedAs($user);
    }

    public function test_last_activity_is_refreshed_after_each_request(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['auth.last_activity_at' => now()->subMinutes(5)->getTimestamp()])
            ->get(route('products.index'));

        $this->assertGreaterThan(
            now()->subMinute()->getTimestamp(),
            session('auth.last_activity_at'),
        );
    }
}
