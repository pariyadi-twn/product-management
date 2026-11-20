<?php

namespace App\Models;

use Database\Factories\AlertFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    /** @use HasFactory<AlertFactory> */
    use HasFactory;

    public const TYPE_ERROR_RATE = 'error_rate';

    public const TYPE_SLOW_SEARCH = 'slow_search';

    public const TYPE_FAILED_LOGIN = 'failed_login';

    public const SEVERITY_INFO = 'info';

    public const SEVERITY_WARNING = 'warning';

    public const SEVERITY_CRITICAL = 'critical';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'severity',
        'message',
        'metric_value',
        'threshold',
        'is_resolved',
        'triggered_at',
        'resolved_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metric_value' => 'decimal:2',
            'threshold' => 'decimal:2',
            'is_resolved' => 'boolean',
            'triggered_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public static function typeLabels(): array
    {
        return [
            self::TYPE_ERROR_RATE => 'Tingkat kesalahan',
            self::TYPE_SLOW_SEARCH => 'Pencarian lambat',
            self::TYPE_FAILED_LOGIN => 'Login gagal berulang',
        ];
    }

    public function typeLabel(): string
    {
        return self::typeLabels()[$this->type] ?? $this->type;
    }

    public function severityLabel(): string
    {
        return match ($this->severity) {
            self::SEVERITY_CRITICAL => 'Kritis',
            self::SEVERITY_WARNING => 'Peringatan',
            default => 'Info',
        };
    }

    public function severityBadgeClass(): string
    {
        return match ($this->severity) {
            self::SEVERITY_CRITICAL => 'text-bg-danger',
            self::SEVERITY_WARNING => 'text-bg-warning',
            default => 'text-bg-info',
        };
    }
}
