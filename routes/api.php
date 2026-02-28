<?php

use App\Http\Controllers\Api\Admin\CalendarController;
use App\Http\Controllers\Api\Admin\ClientController;
use App\Http\Controllers\Api\Admin\ClientPackageController;
use App\Http\Controllers\Api\Admin\DeviceController;
use App\Http\Controllers\Api\Admin\ExpenseController;
use App\Http\Controllers\Api\Admin\FinanceController;
use App\Http\Controllers\Api\Admin\MetaController;
use App\Http\Controllers\Api\Admin\PackageTemplateController;
use App\Http\Controllers\Api\Admin\PaymentController;
use App\Http\Controllers\Api\Admin\ServiceController;
use App\Http\Controllers\Api\Admin\StatsController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\VisitController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['web', 'auth'])->name('admin.')->group(function (): void {
    Route::get('meta', [MetaController::class, 'index'])->name('meta.index');

    Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::post('calendar/quick-visit', [CalendarController::class, 'quickStore'])->name('calendar.quick-visit');

    Route::get('stats', [StatsController::class, 'index'])->name('stats.index');
    Route::get('finance', [FinanceController::class, 'index'])->name('finance.index');

    Route::apiResource('clients', ClientController::class);
    Route::apiResource('services', ServiceController::class);
    Route::apiResource('devices', DeviceController::class);
    Route::apiResource('visits', VisitController::class);
    Route::apiResource('package-templates', PackageTemplateController::class)
        ->parameters(['package-templates' => 'packageTemplate']);
    Route::apiResource('client-packages', ClientPackageController::class)
        ->parameters(['client-packages' => 'clientPackage']);
    Route::apiResource('payments', PaymentController::class);
    Route::apiResource('expenses', ExpenseController::class);
    Route::apiResource('users', UserController::class)->only(['index', 'store']);
});
