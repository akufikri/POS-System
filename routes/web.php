<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

// Auth routes
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Language switcher
Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'id'])) {
        session(['locale' => $locale]);
    }
    return back();
})->name('lang.switch');

// Redirect root to dashboard

Route::get('/', fn() => redirect()->route('dashboard'));

// Protected routes (auth + tenant scope)
Route::middleware(['auth', \App\Http\Middleware\EnsureTenantScope::class])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/summary', [DashboardController::class, 'summary'])->name('dashboard.summary');

    // Products (owner only)
    Route::middleware(\App\Http\Middleware\EnsureRole::class . ':owner')->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        // Categories (owner only)
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });

    // Transactions (owner only)
    Route::middleware(\App\Http\Middleware\EnsureRole::class . ':owner')->group(function () {
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/export', [TransactionController::class, 'export'])->name('transactions.export');
        Route::get('/transactions/{order}', [TransactionController::class, 'show'])->name('transactions.show');
        // Employees (owner only)
        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::put('/employees/{user}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::delete('/employees/{user}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
        Route::patch('/employees/{user}/suspend', [EmployeeController::class, 'suspend'])->name('employees.suspend');

        Route::get('/shifts', [ShiftController::class, 'webIndex'])->name('shifts.index');
        Route::get('/shifts/api/list', [ShiftController::class, 'index'])->name('shifts.api.list');
        // Route::get('/returns', [ReturnController::class, 'index'])->name('returns.index');
    });

    // Cashier-only routes
    Route::middleware(\App\Http\Middleware\EnsureRole::class . ':cashier')->group(function () {
        Route::get('/my-history', [TransactionController::class, 'myHistory'])->name('my-history');
    });

    // Both owner & cashier
    Route::get('/shifts/api/current', [ShiftController::class, 'current'])->name('shifts.api.current');
    Route::get('/shifts/current', function () {
        return view('page.shifts.current');
    })->name('shifts.current');
    Route::get('/shifts/{shift}', [ShiftController::class, 'show'])->name('shifts.show');
    Route::post('/shifts', [ShiftController::class, 'store'])->name('shifts.store');
    Route::post('/shifts/{shift}/close', [ShiftController::class, 'close'])->name('shifts.close');

    // Orders
    Route::post('/orders', [\App\Http\Controllers\OrderController::class, 'store'])->name('orders.store');

    // Stock Management
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::post('/stock', [StockController::class, 'store'])->name('stock.store');

    // Payments & returns (accessible by both roles)
    Route::post('/orders/{order}/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::post('/orders/{order}/returns', [ReturnController::class, 'store'])->name('returns.store');
});
