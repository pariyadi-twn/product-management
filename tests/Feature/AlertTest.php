<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Alert;
use App\Models\PerformanceLog;
use Tests\TestCase;

class AlertTest extends TestCase
{
    public function test_error_rate_alert_is_created_above_threshold(): void
    {
        PerformanceLog::factory()->count(6)->error()->create([
            'duration_ms' => 100,
            'result_count' => null,
            'recorded_at' => now(),
        ]);

        $this->artisan('monitoring:check-alerts')->assertSuccessful();

        $alert = Alert::query()->where('type', Alert::TYPE_ERROR_RATE)->firstOrFail();

        $this->assertSame(Alert::SEVERITY_CRITICAL, $alert->severity);
        $this->assertSame('6.00', $alert->metric_value);
        $this->assertSame('5.00', $alert->threshold);
        $this->assertFalse($alert->is_resolved);
    }

    public function test_error_rate_alert_is_not_created_below_threshold(): void
    {
        PerformanceLog::factory()->count(5)->error()->create([
            'duration_ms' => 100,
            'result_count' => null,
            'recorded_at' => now(),
        ]);

        $this->artisan('monitoring:check-alerts')->assertSuccessful();

        $this->assertDatabaseCount('alerts', 0);
    }

    public function test_slow_search_alert_is_created(): void
    {
        PerformanceLog::factory()->create([
            'endpoint' => '/products',
            'duration_ms' => 700,
            'result_count' => 12,
            'recorded_at' => now(),
        ]);

        $this->artisan('monitoring:check-alerts')->assertSuccessful();

        $alert = Alert::query()->where('type', Alert::TYPE_SLOW_SEARCH)->firstOrFail();

        $this->assertSame(Alert::SEVERITY_WARNING, $alert->severity);
        $this->assertSame('700.00', $alert->metric_value);
        $this->assertSame('500.00', $alert->threshold);
    }

    public function test_failed_login_alert_is_created_after_consecutive_failures(): void
    {
        ActivityLog::factory()->count(11)->create([
            'action' => ActivityLog::ACTION_LOGIN,
            'new_values' => ['email' => 'target@example.com', 'success' => false, 'reason' => 'invalid_credentials'],
            'created_at' => now(),
        ]);

        $this->artisan('monitoring:check-alerts')->assertSuccessful();

        $alert = Alert::query()->where('type', Alert::TYPE_FAILED_LOGIN)->firstOrFail();

        $this->assertSame(Alert::SEVERITY_CRITICAL, $alert->severity);
        $this->assertSame('11.00', $alert->metric_value);
        $this->assertStringContainsString('target@example.com', $alert->message);
    }

    public function test_repeated_checks_do_not_duplicate_unresolved_alerts(): void
    {
        PerformanceLog::factory()->count(7)->error()->create([
            'duration_ms' => 100,
            'result_count' => null,
            'recorded_at' => now(),
        ]);

        $this->artisan('monitoring:check-alerts')->assertSuccessful();
        $this->artisan('monitoring:check-alerts')->assertSuccessful();

        $this->assertDatabaseCount('alerts', 1);
        $this->assertDatabaseHas('alerts', [
            'type' => Alert::TYPE_ERROR_RATE,
            'metric_value' => 7,
        ]);
    }
}
