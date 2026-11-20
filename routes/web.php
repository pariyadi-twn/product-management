<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/products');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', 'idle'])->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::resource('products', ProductController::class);

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/activity-logs/{activityLog}', [ActivityLogController::class, 'show'])->name('activity-logs.show');

    Route::get('/help', [HelpController::class, 'index'])->name('help.index');

    Route::middleware('role:admin')->group(function (): void {
        Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
        Route::patch('/alerts/{alert}/resolve', [AlertController::class, 'resolve'])->name('alerts.resolve');
    });
});
