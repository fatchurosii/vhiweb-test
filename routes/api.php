<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\VendorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
});

Route::group([
    'middleware' => 'auth:sanctum, role:admin',
    'prefix' => 'vendor'
], function () {
    Route::get('', [VendorController::class, 'index']);
    Route::post('', [VendorController::class, 'store']);
    Route::get('/{id}', [VendorController::class, 'show']);
    Route::put('/{id}', [VendorController::class, 'update']);
    Route::put('update-status/{id}', [VendorController::class, 'updateStatus']);
    Route::delete('/{id}', [VendorController::class, 'destroy']);
    Route::patch('verification/{id}', [VendorController::class, 'vendorVerification']);
});

Route::group([
    'prefix' => 'product',
    'middleware' => 'auth:sanctum',
], function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::group([
        'middleware' => 'role:vendor',
    ], function () {
        Route::post('/', [ProductController::class, 'store']);
        Route::get('/{id}', [ProductController::class, 'show']);
        Route::post('/{id}', [ProductController::class, 'update']);
        Route::delete('/{id}', [ProductController::class, 'destroy']);
        Route::patch('update-status/{id}', [ProductController::class, 'updateStatus']);
    });
});

Route::get('/image/{path}', [\App\Http\Controllers\HelperController::class, 'image'])
    ->name('show_file')
    ->withoutMiddleware([
        'auth:sanctum',
        'throttle:250,1'
    ]);
