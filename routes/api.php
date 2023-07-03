<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::post('login',[ AuthController::class, 'login']);
    Route::post('logout',[ AuthController::class, 'logout']);
    Route::post('refresh',[ AuthController::class, 'refresh']);
    Route::post('me',[ AuthController::class, 'me']);
    
});

// products group
Route::group([
    'prefix' => 'products'
], function ($router) {
    Route::get('/', [ProductController::class, 'index'])
    ->withoutMiddleware(['tranform.response']);
    
    Route::get('/{product}', [ProductController::class, 'show']);
    Route::post('/', [ProductController::class, 'store']);
    Route::put('/{product}', [ProductController::class, 'update']);
    Route::delete('/{product}', [ProductController::class, 'destroy']);
});

// order group
Route::group([
    'prefix' => 'orders'
], function ($router) {
    Route::post('/', [OrderController::class,'store']);
    Route::get('/', [OrderController::class,'index']);
    Route::get('/{order_id}', [OrderController::class,'show']);
    // update order status only
    Route::post('/status', [OrderController::class,'updateOrderStatus']);
    Route::post('/tracking/status', [OrderController::class,'updateOrderTrackingStatus']);
    Route::delete('/{order_id}', [OrderController::class,'destroy']);
        
    // refund
    Route::post('/refund',[ OrderController::class, 'refundOrder']);
    // cancel
    Route::post('/cancel',[ OrderController::class, 'cancelOrder']);
    // reorder
    Route::post('/reorder',[ OrderController::class, 'reorder']);
});

// payment method
Route::get('/payment-methods',[ PaymentMethodController::class, 'index']);


