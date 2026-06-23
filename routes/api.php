<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\KoiController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PeralatanController;
use App\Http\Controllers\OrderPeralatanController;
use App\Http\Controllers\RajaOngkirController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

// ==================== AUTH ====================
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);


// ==================== KOI ====================
Route::get('/koi', [KoiController::class, 'index']);
Route::get('/koi/{id}', [KoiController::class, 'show']);


// ==================== PERALATAN ====================
Route::get('/peralatan', [PeralatanController::class, 'index']);


// ==================== RAJAONGKIR ====================
Route::get('/rajaongkir/cities', [RajaOngkirController::class, 'getDaftarKota']);


/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (SANCTUM)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // ==================== USER ====================

    Route::get('/profile', [UserController::class, 'profile']);
    Route::post('/logout', [UserController::class, 'logout']);


    /*
    |--------------------------------------------------------------------------
    | KOI MANAGEMENT
    |--------------------------------------------------------------------------
    */

    Route::post('/koi', [KoiController::class, 'store']);
    Route::post('/koi/{id}', [KoiController::class, 'update']);
    Route::delete('/koi/{id}', [KoiController::class, 'destroy']);


    /*
    |--------------------------------------------------------------------------
    | ORDER KOI
    |--------------------------------------------------------------------------
    */

    // ==================== USER ====================

    // checkout koi
    Route::post('/orders', [OrderController::class, 'store']);

    // lihat pesanan sendiri
    Route::get('/my-orders', [OrderController::class, 'myOrders']);

    // payment
    Route::post('/orders/{id}/pay', [OrderController::class, 'createPayment']);

    // after pay
    Route::post('/orders/{id}/after-pay', [OrderController::class, 'afterPay']);

    // qris
    Route::post('/orders/{id}/generate-qris', [OrderController::class, 'generateQris']);


    // ==================== ADMIN ====================

    // semua pesanan
    Route::get('/orders', [OrderController::class, 'index']);

    // status pesanan
    Route::put(
        '/orders/{id}/status-pesanan',
        [OrderController::class, 'updateStatusPesanan']
    );

    // status pembayaran
    Route::put(
        '/orders/{id}/status-pembayaran',
        [OrderController::class, 'updateStatusPembayaran']
    );

    // ✅ status pengiriman
    Route::put(
        '/orders/{id}/status-pengiriman',
        [OrderController::class, 'updateStatusPengiriman']
    );


    /*
    |--------------------------------------------------------------------------
    | RAJAONGKIR
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/rajaongkir/cost',
        [RajaOngkirController::class, 'getCost']
    );


    /*
    |--------------------------------------------------------------------------
    | PERALATAN MANAGEMENT
    |--------------------------------------------------------------------------
    */

    Route::post('/peralatan', [PeralatanController::class, 'store']);
    Route::post('/peralatan/{id}', [PeralatanController::class, 'update']);
    Route::delete('/peralatan/{id}', [PeralatanController::class, 'destroy']);


    /*
    |--------------------------------------------------------------------------
    | ORDER PERALATAN
    |--------------------------------------------------------------------------
    */

    // ==================== USER ====================

    // checkout peralatan
    Route::post('/orders-peralatan', [OrderPeralatanController::class, 'store']);

    // lihat pesanan sendiri
    Route::get('/my-orders-peralatan', [OrderPeralatanController::class, 'myOrders']);

    // payment
    Route::post(
        '/orders-peralatan/{id}/pay',
        [OrderPeralatanController::class, 'createPayment']
    );

    // after pay
    Route::post(
        '/orders-peralatan/{id}/after-pay',
        [OrderPeralatanController::class, 'afterPay']
    );


    // ==================== ADMIN ====================

    // semua pesanan
    Route::get(
        '/orders-peralatan',
        [OrderPeralatanController::class, 'index']
    );

    // status pesanan
    Route::put(
        '/orders-peralatan/{id}/status-pesanan',
        [OrderPeralatanController::class, 'updateStatusPesanan']
    );

    // status pembayaran
    Route::put(
        '/orders-peralatan/{id}/status-pembayaran',
        [OrderPeralatanController::class, 'updateStatusPembayaran']
    );

    // ✅ status pengiriman
    Route::put(
        '/orders-peralatan/{id}/status-pengiriman',
        [OrderPeralatanController::class, 'updateStatusPengiriman']
    );

});