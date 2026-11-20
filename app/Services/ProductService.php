<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public const PER_PAGE = 15;

    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginate(): LengthAwarePaginator
    {
        return Product::query()
            ->with(['category', 'createdBy', 'updatedBy'])
            ->latest()
            ->paginate(self::PER_PAGE)
            ->withQueryString();
    }

    /**
     * @return array{
     *     total: int,
     *     categories: int,
     *     low_stock: int,
     *     out_of_stock: int,
     *     per_category: Collection<int, Category>
     * }
     */
    public function summary(): array
    {
        $lowStockThreshold = (int) config('monitoring.low_stock_threshold', 10);

        return [
            'total' => Product::query()->count(),
            'categories' => Category::query()->count(),
            'low_stock' => Product::query()
                ->where('stock', '>', 0)
                ->where('stock', '<=', $lowStockThreshold)
                ->count(),
            'out_of_stock' => Product::query()->where('stock', 0)->count(),
            'per_category' => Category::query()
                ->withCount('products')
                ->orderBy('name')
                ->get(),
        ];
    }

    public function findDuplicate(string $field, string $value, ?int $ignoreId = null): ?Product
    {
        return Product::withTrashed()
            ->where($field, $value)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $actor): Product
    {
        return DB::transaction(function () use ($data, $actor): Product {
            $product = Product::query()->create([
                ...$data,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            return $product->load(['category', 'createdBy', 'updatedBy']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data, User $actor): Product
    {
        return DB::transaction(function () use ($product, $data, $actor): Product {
            $product->fill([
                ...$data,
                'updated_by' => $actor->id,
            ]);

            $product->save();

            return $product->load(['category', 'createdBy', 'updatedBy']);
        });
    }

    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product): void {
            $product->delete();
        });
    }
}
