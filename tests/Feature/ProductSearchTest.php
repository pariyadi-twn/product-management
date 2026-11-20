<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSearchTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Category $elektronik;

    private Category $pakaian;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->elektronik = Category::factory()->create(['name' => 'Elektronik']);
        $this->pakaian = Category::factory()->create(['name' => 'Pakaian']);
    }

    public function test_products_can_be_searched_by_name(): void
    {
        Product::factory()->create([
            'name' => 'Laptop Pro 14',
            'category_id' => $this->elektronik->id,
        ]);
        Product::factory()->create([
            'name' => 'Kaos Polos',
            'category_id' => $this->pakaian->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('products.index', ['q' => 'laptop']));

        $response->assertOk();
        $response->assertSee('Laptop Pro 14');
        $response->assertDontSee('Kaos Polos');
    }

    public function test_products_can_be_searched_by_sku(): void
    {
        Product::factory()->create([
            'sku' => 'ELK-999',
            'category_id' => $this->elektronik->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('products.index', ['q' => 'ELK-999']));

        $response->assertOk();
        $response->assertSee('ELK-999');
    }

    public function test_products_can_be_filtered_by_category(): void
    {
        Product::factory()->create([
            'name' => 'Mouse Wireless',
            'category_id' => $this->elektronik->id,
        ]);
        Product::factory()->create([
            'name' => 'Kemeja Formal',
            'category_id' => $this->pakaian->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('products.index', ['category' => $this->pakaian->id]));

        $response->assertOk();
        $response->assertSee('Kemeja Formal');
        $response->assertDontSee('Mouse Wireless');
    }

    public function test_products_can_be_filtered_by_status_and_stock(): void
    {
        Product::factory()->create(['name' => 'Produk Aktif', 'status' => 'aktif', 'stock' => 5, 'category_id' => $this->elektronik->id]);
        Product::factory()->create(['name' => 'Produk Nonaktif', 'status' => 'nonaktif', 'stock' => 5, 'category_id' => $this->elektronik->id]);
        Product::factory()->create(['name' => 'Produk Habis', 'status' => 'aktif', 'stock' => 0, 'category_id' => $this->elektronik->id]);

        $aktif = $this->actingAs($this->user)->get(route('products.index', ['status' => 'aktif']));
        $aktif->assertSee('Produk Aktif');
        $aktif->assertDontSee('Produk Nonaktif');

        $nonaktif = $this->actingAs($this->user)->get(route('products.index', ['status' => 'nonaktif']));
        $nonaktif->assertSee('Produk Nonaktif');
        $nonaktif->assertDontSee('Produk Aktif');

        $habis = $this->actingAs($this->user)->get(route('products.index', ['status' => 'habis']));
        $habis->assertSee('Produk Habis');
        $habis->assertDontSee('Produk Aktif');
    }

    public function test_products_can_be_sorted(): void
    {
        Product::factory()->create(['name' => 'Zebra', 'price' => 300000, 'category_id' => $this->elektronik->id]);
        Product::factory()->create(['name' => 'Alpha', 'price' => 100000, 'category_id' => $this->elektronik->id]);
        Product::factory()->create(['name' => 'Mango', 'price' => 200000, 'category_id' => $this->elektronik->id]);

        $this->actingAs($this->user)
            ->get(route('products.index', ['sort' => 'name_asc']))
            ->assertSeeInOrder(['Alpha', 'Mango', 'Zebra']);

        $this->actingAs($this->user)
            ->get(route('products.index', ['sort' => 'price_asc']))
            ->assertSeeInOrder(['Alpha', 'Mango', 'Zebra']);

        $this->actingAs($this->user)
            ->get(route('products.index', ['sort' => 'price_desc']))
            ->assertSeeInOrder(['Zebra', 'Mango', 'Alpha']);
    }

    public function test_empty_search_result_shows_friendly_message(): void
    {
        Product::factory()->create([
            'name' => 'Laptop Pro',
            'category_id' => $this->elektronik->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('products.index', ['q' => 'tidak-ada-produk-ini']));

        $response->assertOk();
        $response->assertSee('Produk tidak ditemukan');
        $response->assertSee('Hapus Semua Filter');
    }

    public function test_wildcard_characters_are_escaped(): void
    {
        Product::factory()->create(['name' => 'Kaos 100% Katun', 'category_id' => $this->pakaian->id]);
        Product::factory()->create(['name' => 'Kaos Biasa', 'category_id' => $this->pakaian->id]);

        $response = $this->actingAs($this->user)->get(route('products.index', ['q' => '%']));

        $response->assertSee('Kaos 100% Katun');
        $response->assertDontSee('Kaos Biasa');
    }

    public function test_invalid_filters_are_rejected(): void
    {
        $response = $this->actingAs($this->user)->get(route('products.index', [
            'category' => 999999,
            'sort' => 'tidak-valid',
        ]));

        $response->assertSessionHasErrors(['category', 'sort']);
    }

    public function test_pagination_preserves_query_string(): void
    {
        Product::factory()
            ->count(20)
            ->sequence(fn ($sequence) => ['name' => 'Produk Uji '.$sequence->index])
            ->create([
                'category_id' => $this->elektronik->id,
            ]);

        $response = $this->actingAs($this->user)->get(route('products.index', ['q' => 'Produk Uji']));

        $response->assertOk();
        $response->assertSee('q=Produk');
    }
}
