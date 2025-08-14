<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\UserManagementController;
use App\Http\Controllers\Api\CourseManagementController;
use App\Http\Controllers\Api\ReviewManagementController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () {
    Route::post('payment-methods/setup-intent' , [PaymentMethodController::class, 'createSetupIntent']);
    Route::apiResource('payment-methods', PaymentMethodController::class)->except(['show', 'update']);
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
Route::get('dashboard/statistics', [DashboardController::class, 'getDashboardStatistics']);
Route::get('/dashboard/top-rated-courses', [DashboardController::class, 'getTopRatedCourses']);

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/users', [UserManagementController::class, 'index']);   // عرض جميع المستخدمين
    Route::get('/users/{id}', [UserManagementController::class, 'show']);         // عرض بروفايل مستخدم
    Route::patch('/users/toggle-status/{id}', [UserManagementController::class, 'toggleStatus']); // تغيير حالة الحساب
    Route::delete('/users/{id}', [UserManagementController::class, 'destroy']); // حذف حساب
});
Route::get('/courses', [CourseManagementController::class, 'index']);
Route::delete('/courses/{id}', [CourseManagementController::class, 'destroy']);
Route::patch('/courses/approve/{id}', [CourseManagementController::class, 'approve']);
Route::get('/instructors', [UserManagementController::class, 'allInstructors']);
Route::prefix('reviews')->group(function () {
    Route::get('/', [ReviewManagementController::class, 'index']);       // عرض كل الريفيوهات
    Route::get('/{id}', [ReviewManagementController::class, 'show']);   // عرض ريفيو واحد
    Route::delete('/{id}', [ReviewManagementController::class, 'destroy']); // حذف ريفيو
});


Route::middleware('auth:sanctum')->controller(CartController::class)->group(function () {
    // Cart
    Route::get('/cart',  'index');
    Route::post('/cart',   'add');
    Route::delete('/cart/{course}','remove');
});
