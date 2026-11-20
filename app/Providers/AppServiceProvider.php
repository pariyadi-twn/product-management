<?php

namespace App\Providers;

use App\Models\Alert;
use App\Support\PerformanceMetrics;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(PerformanceMetrics::class, fn (): PerformanceMetrics => new PerformanceMetrics);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer('layouts.app', function ($view): void {
            $user = auth()->user();

            $view->with(
                'unresolvedAlertCount',
                $user?->isAdmin()
                    ? Alert::query()->where('is_resolved', false)->count()
                    : 0,
            );
        });
    }
}
