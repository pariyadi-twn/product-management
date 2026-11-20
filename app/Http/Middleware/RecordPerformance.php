<?php

namespace App\Http\Middleware;

use App\Models\PerformanceLog;
use App\Support\PerformanceMetrics;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RecordPerformance
{
    public function __construct(private readonly PerformanceMetrics $metrics) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);
        } catch (Throwable $exception) {
            $this->recordSafely($request, 500);

            throw $exception;
        }

        $this->recordSafely($request, $response->getStatusCode());

        return $response;
    }

    private function recordSafely(Request $request, int $statusCode): void
    {
        try {
            $this->record($request, $statusCode);
        } catch (Throwable $exception) {
            Log::warning('Gagal menyimpan metrik performa.', [
                'path' => $request->path(),
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function record(Request $request, int $statusCode): void
    {
        $path = trim($request->path(), '/');

        if (! $this->shouldRecord($path)) {
            return;
        }

        $requestStartedAt = defined('LARAVEL_START')
            ? (float) constant('LARAVEL_START')
            : (float) ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true));

        $durationMs = $this->metrics->durationMs
            ?? (int) round((microtime(true) - $requestStartedAt) * 1000);

        $resultCount = $this->metrics->resultCount;
        $endpoint = '/'.$path;

        PerformanceLog::query()->create([
            'endpoint' => $endpoint,
            'method' => $request->method(),
            'duration_ms' => max(0, $durationMs),
            'status_code' => $statusCode,
            'result_count' => $resultCount,
            'recorded_at' => now(),
        ]);

        if ($statusCode >= 500) {
            Log::error('Permintaan gagal diproses.', [
                'endpoint' => $endpoint,
                'method' => $request->method(),
                'status_code' => $statusCode,
                'duration_ms' => $durationMs,
            ]);
        }

        $slowThreshold = (int) config('monitoring.alerts.slow_search_ms', 500);

        if ($endpoint === '/products' && $durationMs > $slowThreshold) {
            Log::warning('Pencarian produk lambat.', [
                'duration_ms' => $durationMs,
                'result_count' => $resultCount,
            ]);
        }
    }

    private function shouldRecord(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        foreach ((array) config('monitoring.log_paths', []) as $prefix) {
            if (Str::startsWith($path, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
