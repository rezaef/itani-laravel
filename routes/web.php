<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PageController;
use App\Http\Middleware\AdminOnly;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SensorController;
use App\Http\Controllers\Api\WateringLogController;
use App\Http\Controllers\Api\PeriodController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\HarvestController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReportController;


Route::get('/', fn() => redirect('/index.php'));
Route::view('/login.html', 'login');

// pages
Route::get('/index.php', [DashboardController::class, 'index']);
Route::get('/periode.php', [PageController::class, 'periode']);
Route::get('/users.php', [PageController::class, 'users'])->middleware(AdminOnly::class);
Route::get('/stok.php', [PageController::class, 'stok']);
Route::get('/panen.php', [PageController::class, 'panen']);

// auth legacy
Route::post('/api/login.php', [AuthController::class, 'login']);
Route::get('/api/logout.php', [AuthController::class, 'logout']);

// sensor legacy
Route::post('/api/sensors_insert.php', [SensorController::class, 'insert']);
Route::get('/api/sensors_latest.php', [SensorController::class, 'latest']);
Route::get('/api/sensors_history.php', [SensorController::class, 'history']);

// watering legacy
Route::get('/api/watering_logs.php', [WateringLogController::class, 'index']);
Route::post('/api/watering_logs.php', [WateringLogController::class, 'store']);
Route::get('/api/pump_status_latest.php', [WateringLogController::class, 'pumpStatusLatest']);

// notifications (sensor ambang batas / error)
Route::get('/api/notifications.php', [NotificationController::class, 'index']);
Route::post('/api/notifications.php', [NotificationController::class, 'markRead']);
Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead']);


// periods legacy
Route::get('/api/periods.php', [PeriodController::class, 'index']);
Route::post('/api/periods.php', [PeriodController::class, 'store']);

Route::get('/api/harvests.php', [HarvestController::class, 'index']);
// Route::get('/api/periods/{id}/harvests', [HarvestController::class, 'listByPeriod']);
Route::post('/api/harvests.php', [HarvestController::class, 'store']);
Route::put('/api/harvests.php/{id}', [HarvestController::class, 'update']);
Route::delete('/api/harvests.php/{id}', [HarvestController::class, 'destroy']);
Route::get('/api/harvest_report.csv', [ReportController::class, 'harvestCsv']);


Route::match(['GET','POST'], 'api/seed_stock.php', [StockController::class, 'seeds']);
Route::match(['GET','POST'], 'api/fertilizer_stock.php', [StockController::class, 'fertilizers']);

// users legacy (ADMIN ONLY)
Route::middleware([AdminOnly::class])->group(function () {
    Route::get('/api/users.php', [UserController::class, 'index']);
    Route::post('/api/users.php', [UserController::class, 'store']);
    Route::match(['put','patch'], '/api/users.php', [UserController::class, 'update']);
    Route::delete('/api/users.php', [UserController::class, 'destroy']);
});
