<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    use SoftDeletes;

    public const STATUS_AKTIF = 'aktif';

    public const STATUS_NONAKTIF = 'nonaktif';

    public const STATUS_HABIS = 'habis';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'sku',
        'name',
        'category_id',
        'price',
        'stock',
        'status',
        'description',
        'created_by',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AKTIF);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_AKTIF => 'Aktif',
            default => 'Nonaktif',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_AKTIF => 'text-bg-success',
            default => 'text-bg-secondary',
        };
    }

    public function stockLabel(): string
    {
        if ($this->stock <= 0) {
            return 'Habis';
        }

        if ($this->isLowStock()) {
            return 'Menipis';
        }

        return 'Tersedia';
    }

    public function stockBadgeClass(): string
    {
        if ($this->stock <= 0) {
            return 'text-bg-danger';
        }

        if ($this->isLowStock()) {
            return 'text-bg-warning';
        }

        return 'text-bg-info';
    }

    public function isLowStock(): bool
    {
        $threshold = (int) config('monitoring.low_stock_threshold', 10);

        return $this->stock > 0 && $this->stock <= $threshold;
    }

    public function formattedPrice(): string
    {
        return 'Rp '.number_format((float) $this->price, 0, ',', '.');
    }
}
