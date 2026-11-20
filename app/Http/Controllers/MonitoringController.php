<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Services\PerformanceLogService;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    public function __construct(private readonly PerformanceLogService $performanceLogs) {}

    public function index(): View
    {
        return view('monitoring.index', [
            'summary' => $this->performanceLogs->todaySummary(),
            'alerts' => Alert::query()
                ->orderBy('is_resolved')
                ->orderByDesc('triggered_at')
                ->limit(30)
                ->get(),
            'unresolvedCount' => Alert::query()->where('is_resolved', false)->count(),
        ]);
    }
}
