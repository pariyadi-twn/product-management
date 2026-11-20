<?php

namespace Tests\Unit;

use App\Services\ActivityLogService;
use PHPUnit\Framework\TestCase;

class ActivityLogServiceTest extends TestCase
{
    public function test_changed_values_detects_changed_fields_only(): void
    {
        $service = new ActivityLogService;

        $changes = $service->changedValues(
            ['name' => 'Lama', 'price' => '100000.00', 'stock' => 5],
            ['name' => 'Baru', 'price' => '100000.00', 'stock' => 8],
        );

        $this->assertSame(['name' => 'Lama', 'stock' => 5], $changes['old']);
        $this->assertSame(['name' => 'Baru', 'stock' => 8], $changes['new']);
    }

    public function test_changed_values_returns_empty_when_nothing_changed(): void
    {
        $service = new ActivityLogService;

        $changes = $service->changedValues(
            ['name' => 'Sama', 'price' => '100000.00'],
            ['name' => 'Sama', 'price' => '100000.00'],
        );

        $this->assertSame([], $changes['old']);
        $this->assertSame([], $changes['new']);
    }
}
