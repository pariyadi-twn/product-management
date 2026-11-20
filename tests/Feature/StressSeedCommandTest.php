<?php

namespace Tests\Feature;

use Tests\TestCase;

class StressSeedCommandTest extends TestCase
{
    public function test_stress_products_can_be_seeded_and_cleaned(): void
    {
        $this->artisan('products:stress-seed', ['count' => 25])
            ->assertSuccessful();

        $this->assertDatabaseCount('products', 25);
        $this->assertDatabaseHas('products', ['sku' => 'STR-000001']);
        $this->assertDatabaseHas('products', ['sku' => 'STR-000025']);

        $this->artisan('products:stress-seed', ['--clean' => true])
            ->assertSuccessful();

        $this->assertDatabaseCount('products', 0);
    }

    public function test_stress_seed_is_idempotent_with_fresh_option(): void
    {
        $this->artisan('products:stress-seed', ['count' => 10])->assertSuccessful();
        $this->artisan('products:stress-seed', ['count' => 10, '--fresh' => true])->assertSuccessful();

        $this->assertDatabaseCount('products', 10);
    }
}
