<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DownloadController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
});

Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');
    Route::get('/home', [PageController::class, 'home'])->name('home');
    Route::get('/peta', [PageController::class, 'peta'])->name('peta');
    Route::get('/statistik', [PageController::class, 'statistik'])->name('statistik');

    Route::get('/profil', [ProfileController::class, 'edit'])->name('profil');
    Route::post('/profil', [ProfileController::class, 'update'])->name('profil.update');

    Route::get('/tiket', [TicketController::class, 'create'])->name('tiket.create');
    Route::post('/tiket', [TicketController::class, 'store'])->name('tiket.store');
    Route::get('/tiket/riwayat', [TicketController::class, 'history'])->name('tiket.riwayat');
    Route::get('/tiket/{order}/konfirmasi', [TicketController::class, 'confirmation'])->name('tiket.konfirmasi');
    Route::get('/tiket/{order}/bayar', [TicketController::class, 'payment'])->name('tiket.bayar');
    Route::post('/tiket/{order}/bayar', [TicketController::class, 'pay'])->name('tiket.bayar.store');
    Route::get('/tiket/{order}/e-tiket', [TicketController::class, 'eticket'])->name('tiket.eticket');

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');

        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::post('/users/action', [AdminUserController::class, 'action'])->name('users.action');
        Route::post('/users/bulk', [AdminUserController::class, 'bulk'])->name('users.bulk');
        Route::get('/users/export', [AdminUserController::class, 'export'])->name('users.export');
        Route::get('/admins/create', [AdminUserController::class, 'createAdmin'])->name('admins.create');
        Route::post('/admins', [AdminUserController::class, 'storeAdmin'])->name('admins.store');

        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders');
        Route::post('/orders/action', [AdminOrderController::class, 'action'])->name('orders.action');
        Route::get('/orders/data', [AdminOrderController::class, 'data'])->name('orders.data');
        Route::get('/orders/bulk', [AdminOrderController::class, 'bulkPage'])->name('orders.bulk-page');
        Route::post('/orders/bulk', [AdminOrderController::class, 'bulk'])->name('orders.bulk');
        Route::post('/orders/export', [AdminOrderController::class, 'export'])->name('orders.export');
        Route::get('/orders/export/stats', [AdminOrderController::class, 'stats'])->name('orders.stats');
        Route::post('/orders/auto-confirm', [AdminOrderController::class, 'autoConfirm'])->name('orders.auto-confirm');
        Route::get('/orders/{order}/details', [AdminOrderController::class, 'details'])->name('orders.details');

        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/backup', [SettingsController::class, 'backup'])->name('settings.backup');

        Route::get('/download/{type}/{file}', DownloadController::class)
            ->where('file', '[A-Za-z0-9_.\-]+')
            ->name('download');
    });
});
