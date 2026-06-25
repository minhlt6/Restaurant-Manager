<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerGroupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiningTableController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderDetailController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::get('/login',  [AuthController::class, 'create'])->name('login');
Route::post('/login', [AuthController::class, 'store'])->name('login.store');

Route::middleware('role:staff,manager')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Dining tables — staff can view the floor plan
    Route::get('/dining-tables', [DiningTableController::class, 'index'])->name('dining-tables.index');

    // Orders — core workflow
    Route::get('/orders',                          [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders',                         [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}',                  [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/checkout',       [OrderController::class, 'checkout'])->name('orders.checkout');
    Route::patch('/orders/{order}/cancel',         [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::put('/orders/{order}',                  [OrderController::class, 'update'])->name('orders.update');
    Route::delete('/orders/{order}',               [OrderController::class, 'destroy'])->name('orders.destroy');

    // Order details (AJAX only)
    Route::resource('order-details', OrderDetailController::class);

    // Customers — staff can add/edit
    Route::resource('customers', CustomerController::class);

    // Reservations — staff can create/cancel/receive
    Route::post('/reservations',                            [ReservationController::class, 'store'])->name('reservations.store');
    Route::patch('/reservations/{reservation}/cancel',      [ReservationController::class, 'cancel'])->name('reservations.cancel');
    Route::post('/reservations/{reservation}/receive',      [ReservationController::class, 'receive'])->name('reservations.receive');

    // Manager-only routes
    Route::middleware('role:manager')->group(function (): void {
        Route::resource('employees', EmployeeController::class);
        Route::resource('customer-groups', CustomerGroupController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('items', ItemController::class);

        // Dining tables management (add/edit/delete)
        Route::get('/dining-tables/manage',            [DiningTableController::class, 'manage'])->name('dining-tables.manage');
        Route::post('/dining-tables',                  [DiningTableController::class, 'store'])->name('dining-tables.store');
        Route::put('/dining-tables/{diningTable}',     [DiningTableController::class, 'update'])->name('dining-tables.update');
        Route::delete('/dining-tables/{diningTable}',  [DiningTableController::class, 'destroy'])->name('dining-tables.destroy');
        Route::get('/dining-tables/{diningTable}',     [DiningTableController::class, 'show'])->name('dining-tables.show');

        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    });
});
