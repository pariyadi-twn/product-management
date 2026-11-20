<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActivityLogIndexRequest;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(ActivityLogIndexRequest $request): View
    {
        $filters = $request->filters();

        $logs = ActivityLog::query()
            ->with(['user', 'product'])
            ->when($filters['action'], fn (Builder $query, string $action) => $query->where('action', $action))
            ->when($filters['product'], function (Builder $query, string $keyword): void {
                $escaped = addcslashes($keyword, '%_\\');

                $query->whereHas('product', function (Builder $productQuery) use ($escaped): void {
                    $productQuery->where('name', 'like', '%'.$escaped.'%')
                        ->orWhere('sku', 'like', '%'.$escaped.'%');
                });
            })
            ->when($filters['date_from'], fn (Builder $query, string $date) => $query->where('created_at', '>=', Carbon::parse($date)->startOfDay()))
            ->when($filters['date_to'], fn (Builder $query, string $date) => $query->where('created_at', '<=', Carbon::parse($date)->endOfDay()))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('activity-logs.index', [
            'logs' => $logs,
            'filters' => $filters,
            'hasActiveFilters' => $request->hasActiveFilters(),
            'actionLabels' => ActivityLog::actionLabels(),
        ]);
    }

    public function show(ActivityLog $activityLog): View
    {
        $activityLog->load(['user', 'product']);

        return view('activity-logs.show', [
            'log' => $activityLog,
        ]);
    }
}
