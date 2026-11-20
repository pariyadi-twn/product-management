<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['email' => 'staff@example.com']);
        $this->category = Category::factory()->create();
    }

    public function test_creating_product_is_logged(): void
    {
        $this->actingAs($this->user)->post(route('products.store'), [
            'sku' => 'LOG-001',
            'name' => 'Produk Log',
            'category_id' => $this->category->id,
            'price' => 150000,
            'stock' => 4,
            'status' => 'aktif',
        ]);

        $log = ActivityLog::query()->where('action', ActivityLog::ACTION_CREATE)->firstOrFail();

        $this->assertSame($this->user->id, $log->user_id);
        $this->assertSame('Produk Log', $log->new_values['name']);
        $this->assertNull($log->old_values);
    }

    public function test_updating_product_stores_old_and_new_values(): void
    {
        $product = Product::factory()->create([
            'name' => 'Nama Lama',
            'price' => 100000,
            'stock' => 5,
            'category_id' => $this->category->id,
        ]);

        $this->actingAs($this->user)->put(route('products.update', $product), [
            'sku' => $product->sku,
            'name' => 'Nama Baru',
            'category_id' => $this->category->id,
            'price' => 175000,
            'stock' => 5,
            'status' => 'aktif',
            'description' => null,
        ]);

        $log = ActivityLog::query()->where('action', ActivityLog::ACTION_UPDATE)->firstOrFail();

        $this->assertSame('Nama Lama', $log->old_values['name']);
        $this->assertSame('Nama Baru', $log->new_values['name']);
        $this->assertArrayNotHasKey('stock', $log->new_values);
    }

    public function test_updating_without_changes_does_not_create_log(): void
    {
        $product = Product::factory()->create([
            'name' => 'Tetap',
            'category_id' => $this->category->id,
        ]);

        $this->actingAs($this->user)->put(route('products.update', $product), [
            'sku' => $product->sku,
            'name' => $product->name,
            'category_id' => $product->category_id,
            'price' => $product->price,
            'stock' => $product->stock,
            'status' => $product->status,
            'description' => $product->description,
        ]);

        $this->assertDatabaseMissing('activity_logs', ['action' => ActivityLog::ACTION_UPDATE]);
    }

    public function test_deleting_product_is_logged_with_snapshot(): void
    {
        $product = Product::factory()->create([
            'name' => 'Produk Dihapus',
            'category_id' => $this->category->id,
        ]);

        $this->actingAs($this->user)->delete(route('products.destroy', $product));

        $log = ActivityLog::query()->where('action', ActivityLog::ACTION_DELETE)->firstOrFail();

        $this->assertSame('Produk Dihapus', $log->old_values['name']);
        $this->assertNull($log->new_values);
        $this->assertSoftDeleted($product);
    }

    public function test_login_and_logout_are_logged(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => 'password',
        ]);

        $this->post(route('login.store'), [
            'email' => 'login@example.com',
            'password' => 'password',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => ActivityLog::ACTION_LOGIN,
        ]);

        $this->post(route('logout'));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => ActivityLog::ACTION_LOGOUT,
        ]);
    }

    public function test_failed_login_is_logged(): void
    {
        User::factory()->create([
            'email' => 'gagal@example.com',
            'password' => 'password',
        ]);

        $this->post(route('login.store'), [
            'email' => 'gagal@example.com',
            'password' => 'salah',
        ]);

        $log = ActivityLog::query()->where('action', ActivityLog::ACTION_LOGIN)->firstOrFail();

        $this->assertFalse($log->new_values['success']);
        $this->assertSame('invalid_credentials', $log->new_values['reason']);
    }

    public function test_invalid_search_filter_is_logged(): void
    {
        $this->actingAs($this->user)->get(route('products.index', ['sort' => 'invalid']));

        $this->assertDatabaseHas('activity_logs', [
            'action' => ActivityLog::ACTION_SEARCH_ERROR,
        ]);
    }

    public function test_activity_log_can_be_filtered(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        ActivityLog::factory()->create([
            'action' => ActivityLog::ACTION_CREATE,
            'description' => 'Menambahkan produk uji.',
            'product_id' => $product->id,
            'created_at' => now()->subDays(2),
        ]);

        ActivityLog::factory()->create([
            'action' => ActivityLog::ACTION_LOGIN,
            'description' => 'Masuk ke aplikasi.',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->get(route('activity-logs.index', [
            'action' => ActivityLog::ACTION_CREATE,
        ]));

        $response->assertOk();
        $response->assertSee('Menambahkan produk uji.');
        $response->assertDontSee('Masuk ke aplikasi.');

        $response = $this->actingAs($this->user)->get(route('activity-logs.index', [
            'product' => $product->name,
        ]));

        $response->assertSee('Menambahkan produk uji.');
    }

    public function test_activity_log_detail_shows_diff(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $log = ActivityLog::factory()->create([
            'action' => ActivityLog::ACTION_UPDATE,
            'product_id' => $product->id,
            'old_values' => ['name' => 'Nama Lama', 'price' => '100000.00'],
            'new_values' => ['name' => 'Nama Baru', 'price' => '125000.00'],
        ]);

        $response = $this->actingAs($this->user)->get(route('activity-logs.show', $log));

        $response->assertOk();
        $response->assertSee('Nama Lama');
        $response->assertSee('Nama Baru');
        $response->assertSee('Rp 125.000');
    }
}
