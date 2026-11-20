<?php

namespace App\Services;

use App\Exceptions\DuplicateProductException;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public const PER_PAGE = 15;

    public function __construct(private readonly ActivityLogService $activityLogs) {}

    /**
     * @param  array{q?: string|null, category?: int|null, status?: string|null, sort?: string|null}  $filters
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = Product::query()
            ->with(['category', 'createdBy', 'updatedBy']);

        $keyword = trim((string) ($filters['q'] ?? ''));

        if ($keyword !== '') {
            $escaped = addcslashes($keyword, '%_\\');

            $query->where(function (Builder $query) use ($escaped): void {
                $query->where('name', 'like', '%'.$escaped.'%')
                    ->orWhere('sku', 'like', '%'.$escaped.'%');
            });
        }

        if (! empty($filters['category'])) {
            $query->where('category_id', $filters['category']);
        }

        $status = $filters['status'] ?? null;

        if ($status === Product::STATUS_HABIS) {
            $query->where('stock', 0);
        } elseif (in_array($status, [Product::STATUS_AKTIF, Product::STATUS_NONAKTIF], true)) {
            $query->where('status', $status);
        }

        match ($filters['sort'] ?? 'newest') {
            'name_asc' => $query->orderBy('name'),
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            default => $query->latest(),
        };

        return $query->paginate(self::PER_PAGE)->withQueryString();
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
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $actor): Product
    {
        return DB::transaction(function () use ($data, $actor): Product {
            $this->ensureUnique($data);

            try {
                $product = Product::query()->create([
                    ...$data,
                    'created_by' => $actor->id,
                    'updated_by' => $actor->id,
                ]);
            } catch (QueryException $exception) {
                $this->convertUniqueViolation($exception, $data);
            }

            $product->load(['category', 'createdBy', 'updatedBy']);

            $this->activityLogs->record(
                ActivityLog::ACTION_CREATE,
                $actor,
                $product,
                null,
                $this->activityLogs->snapshot($product),
                'Menambahkan produk "'.$product->name.'".',
            );

            return $product;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data, User $actor): Product
    {
        return DB::transaction(function () use ($product, $data, $actor): Product {
            $this->ensureUnique($data, $product->id);

            $before = $this->activityLogs->snapshot($product);

            $product->fill([
                ...$data,
                'updated_by' => $actor->id,
            ]);

            try {
                $product->save();
            } catch (QueryException $exception) {
                $this->convertUniqueViolation($exception, $data, $product->id);
            }

            $product->load(['category', 'createdBy', 'updatedBy']);

            $changes = $this->activityLogs->changedValues($before, $this->activityLogs->snapshot($product));

            if ($changes['new'] !== []) {
                $this->activityLogs->record(
                    ActivityLog::ACTION_UPDATE,
                    $actor,
                    $product,
                    $changes['old'],
                    $changes['new'],
                    'Mengubah produk "'.$product->name.'".',
                );
            }

            return $product;
        });
    }

    public function delete(Product $product, ?User $actor = null): void
    {
        DB::transaction(function () use ($product, $actor): void {
            $snapshot = $this->activityLogs->snapshot($product);

            $product->delete();

            $this->activityLogs->record(
                ActivityLog::ACTION_DELETE,
                $actor,
                $product,
                $snapshot,
                null,
                'Menghapus produk "'.$product->name.'".',
            );
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function ensureUnique(array $data, ?int $ignoreId = null): void
    {
        foreach (['name', 'sku'] as $field) {
            if (! isset($data[$field])) {
                continue;
            }

            $duplicate = $this->findDuplicate($field, (string) $data[$field], $ignoreId);

            if ($duplicate !== null) {
                throw new DuplicateProductException($field, $duplicate);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function convertUniqueViolation(QueryException $exception, array $data, ?int $ignoreId = null): void
    {
        if ((int) ($exception->errorInfo[1] ?? 0) !== 1062) {
            throw $exception;
        }

        foreach (['name', 'sku'] as $field) {
            if (! isset($data[$field])) {
                continue;
            }

            $duplicate = $this->findDuplicate($field, (string) $data[$field], $ignoreId);

            if ($duplicate !== null) {
                throw new DuplicateProductException($field, $duplicate);
            }
        }

        throw $exception;
    }
}
