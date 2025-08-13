<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () {
    Route::post('payment-methods/setup-intent', [PaymentMethodController::class, 'createSetupIntent']);
    Route::apiResource('payment-methods', PaymentMethodController::class);
});

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/verify-code', [RegisterController::class, 'verifyCode']);

Route::post('/login', [LoginController::class, 'login']);

// Social Login - Google
Route::get('/auth/google/redirect', [RegisterController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [RegisterController::class, 'handleGoogleCallback']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', function (Request $request) {
        return $request->user();
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout']);
});
