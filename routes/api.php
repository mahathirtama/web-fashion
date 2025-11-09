<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductSellController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// === ROUTES UNTUK LOGIN ===
Route::post('/login', [AuthController::class, 'login']);

// === ROUTES UNTUK REPORT ===
Route::get('/reports', [ReportController::class, 'index']);

// === ROUTES UNTUK USER ===
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);

// === ROUTES UNTUK PRODUCTS IN SELL===
Route::get('/products/sell', [ProductSellController::class, 'index']);

// === ROUTES UNTUK PRODUCTS IN INVENTORY===
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::post('/products', [ProductController::class, 'store']);
Route::put('/products/{id}', [ProductController::class, 'update']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);

// === ROUTES UNTUK SUPPLIERS ===
Route::get('/suppliers', [SupplierController::class, 'index']);
Route::get('/suppliers/{id}', [SupplierController::class, 'show']);
Route::post('/suppliers', [SupplierController::class, 'store']);
Route::put('/suppliers/{id}', [SupplierController::class, 'update']);
Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy']);

// === ROUTES UNTUK SALES ===
Route::get('/sales', [SalesController::class, 'index']);
Route::get('/sales/{id}', [SalesController::class, 'show']);
Route::post('/sales', [SalesController::class, 'store']);
Route::put('/sales/{id}', [SalesController::class, 'update']);
Route::delete('/sales/{id}', [SalesController::class, 'destroy']);