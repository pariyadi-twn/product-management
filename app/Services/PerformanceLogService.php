<?php

namespace App\Services;

use App\Models\PerformanceLog;
use Illuminate\Support\Collection;

class PerformanceLogService
{
    /**
     * @return array{
     *     operations: int,
     *     average_duration: int,
     *     errors: int,
     *     fastest_search: int|null,
     *     slowest_search: int|null,
     *     recent: Collection<int, PerformanceLog>
     * }
     */
    public function todaySummary(): array
    {
        $start = now()->startOfDay();
        $end = now()->endOfDay();

        $baseQuery = PerformanceLog::query()
            ->whereBetween('recorded_at', [$start, $end]);

        return [
            'operations' => (clone $baseQuery)->count(),
            'average_duration' => (int) round((clone $baseQuery)->avg('duration_ms') ?? 0),
            'errors' => (clone $baseQuery)->where('status_code', '>=', 500)->count(),
            'fastest_search' => (clone $baseQuery)
                ->where('endpoint', '/products')
                ->whereNotNull('result_count')
                ->min('duration_ms'),
            'slowest_search' => (clone $baseQuery)
                ->where('endpoint', '/products')
                ->whereNotNull('result_count')
                ->max('duration_ms'),
            'recent' => (clone $baseQuery)
                ->orderByDesc('recorded_at')
                ->orderByDesc('id')
                ->limit(15)
                ->get(),
        ];
    }
}
