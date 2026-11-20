<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\Category;
use App\Models\PerformanceLog;
use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class MonitoringTest extends TestCase
{
    public function test_product_catalog_request_records_performance_metric(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        Product::factory()
            ->count(3)
            ->sequence(fn ($sequence) => ['name' => 'Produk Uji '.$sequence->index])
            ->create(['category_id' => $category->id]);

        $this->actingAs($user)->get(route('products.index', ['q' => 'produk']));

        $log = PerformanceLog::query()->where('endpoint', '/products')->firstOrFail();

        $this->assertSame('GET', $log->method);
        $this->assertSame(200, $log->status_code);
        $this->assertSame(3, $log->result_count);
        $this->assertGreaterThanOrEqual(0, $log->duration_ms);
    }

    public function test_login_request_records_performance_metric(): void
    {
        User::factory()->create([
            'email' => 'metric@example.com',
            'password' => 'password',
        ]);

        $this->post(route('login.store'), [
            'email' => 'metric@example.com',
            'password' => 'password',
        ]);

        $this->assertDatabaseHas('performance_logs', [
            'endpoint' => '/login',
            'method' => 'POST',
            'status_code' => 302,
        ]);
    }

    public function test_admin_can_view_monitoring_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        PerformanceLog::factory()->create([
            'endpoint' => '/products',
            'duration_ms' => 120,
            'result_count' => 4,
            'recorded_at' => now(),
        ]);

        $alert = Alert::factory()->create([
            'message' => 'Pencarian sangat lambat.',
        ]);

        $response = $this->actingAs($admin)->get(route('monitoring.index'));

        $response->assertOk();
        $response->assertSee('Pantau Performa');
        $response->assertSee('Pencarian sangat lambat.');
        $response->assertSee('120 ms');
        $response->assertSee($alert->typeLabel());
    }

    public function test_staff_cannot_view_monitoring_dashboard(): void
    {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)->get(route('monitoring.index'))->assertForbidden();
    }

    public function test_admin_can_resolve_alert(): void
    {
        $admin = User::factory()->admin()->create();
        $alert = Alert::factory()->create();

        $response = $this->actingAs($admin)->patch(route('alerts.resolve', $alert));

        $response->assertRedirect(route('monitoring.index'));
        $this->assertTrue($alert->fresh()->is_resolved);
        $this->assertNotNull($alert->fresh()->resolved_at);
    }

    public function test_staff_cannot_resolve_alert(): void
    {
        $staff = User::factory()->staff()->create();
        $alert = Alert::factory()->create();

        $this->actingAs($staff)->patch(route('alerts.resolve', $alert))->assertForbidden();
        $this->assertFalse($alert->fresh()->is_resolved);
    }
}
