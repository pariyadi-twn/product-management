<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Alert;
use App\Models\PerformanceLog;
use Illuminate\Support\Carbon;

class AlertService
{
    /**
     * @return list<Alert>
     */
    public function evaluate(): array
    {
        return array_values(array_filter([
            $this->checkErrorRate(),
            $this->checkSlowSearch(),
            $this->checkFailedLogins(),
        ]));
    }

    public function resolve(Alert $alert): Alert
    {
        $alert->update([
            'is_resolved' => true,
            'resolved_at' => now(),
        ]);

        return $alert;
    }

    public function unresolvedCount(): int
    {
        return Alert::query()->where('is_resolved', false)->count();
    }

    private function checkErrorRate(): ?Alert
    {
        $threshold = (int) config('monitoring.alerts.error_threshold', 5);
        $windowStart = $this->windowStart();

        $errorCount = PerformanceLog::query()
            ->where('recorded_at', '>=', $windowStart)
            ->where('status_code', '>=', 500)
            ->count();

        if ($errorCount <= $threshold) {
            return null;
        }

        return $this->upsert(
            Alert::TYPE_ERROR_RATE,
            Alert::SEVERITY_CRITICAL,
            'Terdeteksi '.$errorCount.' kesalahan server dalam '.$this->windowMinutes().' menit terakhir.',
            $errorCount,
            $threshold,
        );
    }

    private function checkSlowSearch(): ?Alert
    {
        $threshold = (int) config('monitoring.alerts.slow_search_ms', 500);

        $slowest = PerformanceLog::query()
            ->where('recorded_at', '>=', $this->windowStart())
            ->where('endpoint', '/products')
            ->whereNotNull('result_count')
            ->where('duration_ms', '>', $threshold)
            ->orderByDesc('duration_ms')
            ->first();

        if ($slowest === null) {
            return null;
        }

        return $this->upsert(
            Alert::TYPE_SLOW_SEARCH,
            Alert::SEVERITY_WARNING,
            'Pencarian produk membutuhkan '.$slowest->duration_ms.' ms (ambang batas '.$threshold.' ms).',
            $slowest->duration_ms,
            $threshold,
        );
    }

    private function checkFailedLogins(): ?Alert
    {
        $threshold = (int) config('monitoring.alerts.failed_login_threshold', 10);

        $logs = ActivityLog::query()
            ->where('action', ActivityLog::ACTION_LOGIN)
            ->where('created_at', '>=', $this->windowStart())
            ->orderByDesc('created_at')
            ->limit(500)
            ->get(['new_values', 'created_at']);

        $maxConsecutive = 0;
        $maxEmail = null;

        foreach ($logs->groupBy(fn (ActivityLog $log): string => (string) ($log->new_values['email'] ?? 'tidak-dikenal')) as $email => $entries) {
            $consecutive = 0;

            foreach ($entries as $entry) {
                if (($entry->new_values['success'] ?? true) === true) {
                    break;
                }

                $consecutive++;
            }

            if ($consecutive > $maxConsecutive) {
                $maxConsecutive = $consecutive;
                $maxEmail = $email;
            }
        }

        if ($maxConsecutive <= $threshold) {
            return null;
        }

        return $this->upsert(
            Alert::TYPE_FAILED_LOGIN,
            Alert::SEVERITY_CRITICAL,
            'Terdeteksi '.$maxConsecutive.' login gagal berturut-turut untuk email '.$maxEmail.'.',
            $maxConsecutive,
            $threshold,
        );
    }

    private function upsert(
        string $type,
        string $severity,
        string $message,
        int|float $metricValue,
        int|float $threshold,
    ): Alert {
        $alert = Alert::query()
            ->where('type', $type)
            ->where('is_resolved', false)
            ->first();

        $attributes = [
            'severity' => $severity,
            'message' => mb_substr($message, 0, 255),
            'metric_value' => $metricValue,
            'threshold' => $threshold,
            'triggered_at' => now(),
        ];

        if ($alert !== null) {
            $alert->update($attributes);

            return $alert;
        }

        return Alert::query()->create([
            'type' => $type,
            'is_resolved' => false,
            'resolved_at' => null,
            ...$attributes,
        ]);
    }

    private function windowStart(): Carbon
    {
        return now()->subMinutes($this->windowMinutes());
    }

    private function windowMinutes(): int
    {
        return (int) config('monitoring.alerts.window_minutes', 15);
    }
}
