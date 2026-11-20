<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    public function test_guest_cannot_access_protected_pages(): void
    {
        $this->get(route('products.index'))->assertRedirect(route('login'));
        $this->get(route('activity-logs.index'))->assertRedirect(route('login'));
        $this->get(route('monitoring.index'))->assertRedirect(route('login'));
        $this->get(route('help.index'))->assertRedirect(route('login'));
    }

    public function test_staff_can_access_catalog_activity_and_help(): void
    {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)->get(route('products.index'))->assertOk();
        $this->actingAs($staff)->get(route('activity-logs.index'))->assertOk();
        $this->actingAs($staff)->get(route('help.index'))->assertOk();
    }

    public function test_staff_cannot_access_admin_routes(): void
    {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)->get(route('monitoring.index'))->assertForbidden();
    }

    public function test_admin_can_access_admin_routes(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('monitoring.index'))->assertOk();
    }
}
