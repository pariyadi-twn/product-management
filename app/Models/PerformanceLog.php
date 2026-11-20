<?php

namespace App\Models;

use Database\Factories\PerformanceLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceLog extends Model
{
    /** @use HasFactory<PerformanceLogFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'endpoint',
        'method',
        'duration_ms',
        'status_code',
        'result_count',
        'recorded_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'duration_ms' => 'integer',
            'status_code' => 'integer',
            'result_count' => 'integer',
            'recorded_at' => 'datetime',
        ];
    }

    public function isError(): bool
    {
        return $this->status_code >= 500;
    }

    public function statusBadgeClass(): string
    {
        return match (true) {
            $this->status_code >= 500 => 'text-bg-danger',
            $this->status_code >= 400 => 'text-bg-warning',
            default => 'text-bg-success',
        };
    }
}
