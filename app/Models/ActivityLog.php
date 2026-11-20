<?php

namespace App\Models;

use Database\Factories\ActivityLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    /** @use HasFactory<ActivityLogFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    public const ACTION_CREATE = 'create';

    public const ACTION_UPDATE = 'update';

    public const ACTION_DELETE = 'delete';

    public const ACTION_LOGIN = 'login';

    public const ACTION_LOGOUT = 'logout';

    public const ACTION_SEARCH_ERROR = 'search_error';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'product_id',
        'action',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public static function actionLabels(): array
    {
        return [
            self::ACTION_CREATE => 'Tambah produk',
            self::ACTION_UPDATE => 'Ubah produk',
            self::ACTION_DELETE => 'Hapus produk',
            self::ACTION_LOGIN => 'Masuk',
            self::ACTION_LOGOUT => 'Keluar',
            self::ACTION_SEARCH_ERROR => 'Pencarian gagal',
        ];
    }

    public function actionLabel(): string
    {
        return self::actionLabels()[$this->action] ?? $this->action;
    }

    public function actionBadgeClass(): string
    {
        return match ($this->action) {
            self::ACTION_CREATE => 'text-bg-success',
            self::ACTION_UPDATE => 'text-bg-primary',
            self::ACTION_DELETE => 'text-bg-danger',
            self::ACTION_LOGIN => $this->isFailedLogin() ? 'text-bg-warning' : 'text-bg-info',
            self::ACTION_LOGOUT => 'text-bg-secondary',
            default => 'text-bg-dark',
        };
    }

    public function isFailedLogin(): bool
    {
        return $this->action === self::ACTION_LOGIN
            && ($this->new_values['success'] ?? true) === false;
    }

    /**
     * @return array<string, string>
     */
    public function contextSummary(): array
    {
        $values = $this->new_values ?? $this->old_values ?? [];

        return collect($values)
            ->only(['email', 'reason', 'filters', 'errors', 'error'])
            ->map(fn (mixed $value): string => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value)
            ->all();
    }
}
