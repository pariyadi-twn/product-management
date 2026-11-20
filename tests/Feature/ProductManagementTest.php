<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    private User $user;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->category = Category::factory()->create(['name' => 'Elektronik']);
    }

    public function test_catalog_can_be_rendered(): void
    {
        $product = Product::factory()->create([
            'name' => 'Laptop Pro',
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('products.index'));

        $response->assertOk();
        $response->assertSee($product->name);
        $response->assertSee($product->sku);
    }

    public function test_product_detail_can_be_rendered(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $response = $this->actingAs($this->user)->get(route('products.show', $product));

        $response->assertOk();
        $response->assertSee($product->name);
    }

    public function test_product_can_be_created(): void
    {
        $response = $this->actingAs($this->user)->post(route('products.store'), [
            'sku' => 'ELK-100',
            'name' => 'Produk Baru',
            'category_id' => $this->category->id,
            'price' => 150000,
            'stock' => 10,
            'status' => 'aktif',
            'description' => 'Deskripsi produk baru.',
        ]);

        $product = Product::query()->where('sku', 'ELK-100')->firstOrFail();

        $response->assertRedirect(route('products.show', $product));
        $this->assertSame($this->user->id, $product->created_by);
        $this->assertSame($this->user->id, $product->updated_by);
    }

    public function test_product_can_be_updated(): void
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'created_by' => $this->user->id,
        ]);
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)->put(route('products.update', $product), [
            'sku' => $product->sku,
            'name' => 'Nama Produk Baru',
            'category_id' => $this->category->id,
            'price' => 200000,
            'stock' => 25,
            'status' => 'nonaktif',
            'description' => 'Deskripsi terbaru.',
        ]);

        $response->assertRedirect(route('products.show', $product));
        $this->assertSame('Nama Produk Baru', $product->fresh()->name);
        $this->assertSame($otherUser->id, $product->fresh()->updated_by);
    }

    public function test_product_is_soft_deleted(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $response = $this->actingAs($this->user)->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));
        $this->assertSoftDeleted($product);
    }

    public function test_product_requires_valid_data(): void
    {
        $response = $this->actingAs($this->user)->post(route('products.store'), [
            'sku' => '',
            'name' => '',
            'category_id' => 999999,
            'price' => -1,
            'stock' => -5,
            'status' => 'tidak-valid',
        ]);

        $response->assertSessionHasErrors(['sku', 'name', 'category_id', 'price', 'stock', 'status']);
    }

    public function test_duplicate_name_and_sku_are_rejected(): void
    {
        Product::factory()->create([
            'sku' => 'ELK-001',
            'name' => 'Produk Lama',
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('products.store'), [
            'sku' => 'ELK-001',
            'name' => 'Produk Lama',
            'category_id' => $this->category->id,
            'price' => 10000,
            'stock' => 1,
            'status' => 'aktif',
        ]);

        $response->assertSessionHasErrors('name');
        $response->assertSessionHas('duplicate_product_name', 'Produk Lama');
        $this->assertDatabaseCount('products', 1);
    }
}
