<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Front\FrontPosController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchasingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SalesController;
// Impor controller auth front-end Anda
use App\Http\Controllers\Front\FrontAuthController;
use App\Http\Controllers\Front\FrontInventoryController;
use App\Http\Controllers\Front\FrontInvoiceController;
use App\Http\Controllers\Front\FrontReportController;
use App\Http\Controllers\Front\FrontUserManagementController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/login', function () {
    return view('auth.login');
})->name('login');



Route::post('/login', [FrontAuthController::class, 'login'])->middleware('guest');


Route::post('/logout', [FrontAuthController::class, 'logout'])->middleware('frontauth')->name('logout');


Route::middleware('frontauth')->group(function () {

    Route::get('/', function () {
        return view('dashboard.index');
    })->name('dashboard.index');

    Route::get('/dashboard', function () {
        return view('dashboard.index');
    });

    // Route untuk menampilkan daftar inventaris
    Route::get('/inventory', [FrontInventoryController::class, 'index'])
        ->name('inventory.index');

    // Route untuk menampilkan form tambah produk baru
    Route::get('/inventory/create', [FrontInventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory/store', [FrontInventoryController::class, 'store'])->name('inventory.store');
    Route::get('/edit/{id}', [FrontInventoryController::class, 'edit'])->name('inventory.edit');
    Route::put('/update/{id}', [FrontInventoryController::class, 'update'])->name('inventory.update');

    // Route untuk menampilkan daftar invoices
    Route::prefix('invoices')->group(function () {
        Route::get('/', [FrontInvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/show/{id}', [FrontInvoiceController::class, 'show'])->name('invoices.show');
        Route::delete('/delete/{id}', [FrontInvoiceController::class, 'destroy'])->name('invoices.destroy');
    });

    // Route untuk menampilkan halaman laporan
    Route::get('/reports', [FrontReportController::class, 'index'])->name('reports.index');

    Route::get('/pos', [FrontPosController::class, 'index'])->name('pos.index');
    Route::post('/pos/process-payment', [FrontPosController::class, 'processPayment'])->name('pos.processPayment');

    Route::get('/purchasing', [PurchasingController::class, 'index'])->name('purchasing.index');
    Route::get('/purchasing/create', [PurchasingController::class, 'create'])->name('purchasing.create');

    Route::prefix('users')->group(function () {
        Route::get('/', [FrontUserManagementController::class, 'index'])->name('users.index');
         Route::get('/create', [FrontUserManagementController::class, 'create'])->name('users.create');
    Route::post('/store', [FrontUserManagementController::class, 'store'])->name('users.store');

    Route::get('/edit/{id}', [FrontUserManagementController::class, 'edit'])->name('users.edit');
    Route::put('/update/{id}', [FrontUserManagementController::class, 'update'])->name('users.update');

    Route::delete('/delete/{id}', [FrontUserManagementController::class, 'destroy'])->name('users.destroy');
    });



    // Route untuk menampilkan form edit inventory


});
