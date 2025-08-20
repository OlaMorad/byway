<?php

use App\Http\Controllers\TeacherProfileController;
use App\Http\Controllers\CourseController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\PaymentHistoryController;
use App\Http\Controllers\Api\UserManagementController;
use App\Http\Controllers\Api\CourseManagementController;
use App\Http\Controllers\Api\ReportsController;
use App\Http\Controllers\Api\ReviewManagementController;
use App\Http\Controllers\Api\LearnerCourseController;
use App\Http\Controllers\Api\Learner\CourseInteractionController;
use App\Http\Controllers\Api\CourseShowController;
use App\Http\Controllers\Api\InstructorRevenueController;
use App\Http\Controllers\Api\TeacherNotificationController;
use App\Http\Controllers\Api\Learner\CourseProgressController;
use App\Http\Controllers\Api\PlatformSettingsController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () {
    Route::post('payment-methods/setup-intent', [PaymentMethodController::class, 'createSetupIntent']);
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

Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
});



Route::get('/teacher/profile', [TeacherProfileController::class, 'show']);
Route::post('/teacher/profile/{id}', [TeacherProfileController::class, 'update'])->middleware('auth:sanctum');
Route::post('/teacher/profile', [TeacherProfileController::class, 'store']);


//store course//
Route::post('/courses', [CourseController::class, 'store']);

// Admin
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard')->group(function () {
        Route::get('/statistics', [DashboardController::class, 'getDashboardStatistics']);
        Route::get('/top-rated-courses', [DashboardController::class, 'getTopRatedCourses']);
        Route::get('/recent-payments', [DashboardController::class, 'getRecentPayments']);
        Route::get('/revenue-report', [DashboardController::class, 'getRevenueReport']);      // تقرير الإيرادات حسب السنة والشهر
    });
    /*
    |--------------------------------------------------------------------------
    | User Management Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('users')->group(function () {
        Route::get('/', [UserManagementController::class, 'index']);                // عرض جميع المستخدمين
        Route::get('/search', [UserManagementController::class, 'searchUsers']);   // البحث عن مستخدمين
        Route::get('/{id}', [UserManagementController::class, 'show']);            // عرض بروفايل مستخدم محدد
        Route::patch('/toggle-status/{id}', [UserManagementController::class, 'toggleStatus']); // تغيير حالة الحساب
        Route::patch('/{userId}', [UserManagementController::class, 'updateUser']); // تعديل بيانات المستخدم
        Route::delete('/{id}', [UserManagementController::class, 'destroy']);       // حذف مستخدم
    });

    /*
    |--------------------------------------------------------------------------
    | Instructors Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/instructors', [UserManagementController::class, 'allInstructors']); // عرض كل المعلمين
    Route::post('/instructors', [UserManagementController::class, 'addInstructor']); // إضافة معلم جديد
    Route::put('/instructors/{id}', [UserManagementController::class, 'updateInstructorProfile']); // تعديل بيانات المعلم
    Route::get('/instructors/search', [UserManagementController::class, 'searchInstructors']);

    /*
    |--------------------------------------------------------------------------
    | Courses Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('courses')->group(function () {
        Route::get('/', [CourseManagementController::class, 'index']);             // عرض كل الكورسات
        Route::get('/{id}', [CourseController::class, 'show']);                     // عرض كورس محدد
        Route::put('/{courseId}', [CourseManagementController::class, 'update']);   // تعديل كورس
        Route::delete('/{courseId}', [CourseManagementController::class, 'destroy']); // حذف كورس
        Route::patch('/approve/{id}', [CourseManagementController::class, 'approve']); // اعتماد كورس
        Route::get('/search', [CourseManagementController::class, 'search']);      // البحث عن كورس
    });

    /*
    |--------------------------------------------------------------------------
    | Reviews Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('reviews')->group(function () {
        Route::get('/', [ReviewManagementController::class, 'index']);          // عرض كل الريفيوهات
        Route::get('/{id}', [ReviewManagementController::class, 'show']);      // عرض ريفيو محدد
        Route::delete('/{id}', [ReviewManagementController::class, 'destroy']); // حذف ريفيو
        Route::get('/search', [ReviewManagementController::class, 'search']);   // البحث عن ريفيو
    });

    /*
    |--------------------------------------------------------------------------
    | Categories Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('categories')->group(function () {
        Route::get('/', [PlatformSettingsController::class, 'index']);        // جلب كل الكاتيجوريز
        Route::post('/', [PlatformSettingsController::class, 'store']);       // إضافة كاتيجوري جديدة
        Route::put('/{id}', [PlatformSettingsController::class, 'update']);   // تعديل اسم الكاتيجوري
        Route::delete('/{id}', [PlatformSettingsController::class, 'destroy']); // حذف كاتيجوري
    });

    /*
    |--------------------------------------------------------------------------
    | Platform Settings Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('settings')->group(function () {
        Route::get('/', [PlatformSettingsController::class, 'showSettings']);  // عرض الإعدادات
        Route::put('/', [PlatformSettingsController::class, 'editSettings']);  // تعديل الإعدادات
    });

    /*
    |--------------------------------------------------------------------------
    | Reports Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportsController::class, 'generalStatistics']);     // الإحصائيات العامة
        Route::get('/courses', [ReportsController::class, 'coursesAvgRating']); // متوسط تقييم الكورسات
        Route::get('/download', [ReportsController::class, 'downloadPdfReport']);
    });
});

Route::middleware('auth:sanctum')->controller(CartController::class)->group(function () {
    // Cart
    Route::get('/cart',  'index');
    Route::post('/cart',   'add');
    Route::delete('/cart/{course}', 'remove');
});

Route::post('/checkout',   [CheckoutController::class, 'checkout'])->middleware('auth:sanctum');
Route::post('/checkout/confirm', [CheckoutController::class, 'confirmWithSavedPM'])->middleware('auth:sanctum');
Route::get('/payment-history', PaymentHistoryController::class)->middleware('auth:sanctum');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/instructor/revenue-analytics', [InstructorRevenueController::class, 'analytics']);
});


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/teacher/notifications',            [TeacherNotificationController::class, 'index']);
    Route::post('/teacher/notifications/mark-all',   [TeacherNotificationController::class, 'markAllAsRead']);
    Route::post('/teacher/notifications/{id}/read',  [TeacherNotificationController::class, 'markAsRead']);
    Route::delete('/teacher/notifications/{id}',        [TeacherNotificationController::class, 'destroy']);
    Route::delete('/teacher/notifications',            [TeacherNotificationController::class, 'destroyAll']);
});





Route::middleware('auth:sanctum')->group(function () {
    Route::get('/learner/courses', [LearnerCourseController::class, 'index']);
});

Route::middleware('auth:sanctum')->prefix('profile')->group(function () {
    Route::post('/close-account', [ProfileController::class, 'closeAccount']);
    Route::get('/status', [ProfileController::class, 'status']);
});

Route::middleware('auth:sanctum')->prefix('learner')->group(function () {
    // Favorites
    Route::post('/favorites/add', [CourseInteractionController::class, 'addToFavorites']);
    Route::post('/favorites/remove', [CourseInteractionController::class, 'removeFromFavorites']);
    Route::get('/favorites', [CourseInteractionController::class, 'getFavorites']);

    // Cart
    Route::post('/cart/add', [CourseInteractionController::class, 'addToCart']);
    Route::post('/cart/remove', [CourseInteractionController::class, 'removeFromCart']);
    Route::get('/cart', [CourseInteractionController::class, 'getCart']);
});

// Public route – no login required
Route::get('/courses/{id}', [CourseShowController::class, 'show']);

Route::middleware('auth:sanctum')->prefix('learner')->group(function () {
    // Mark lesson as completed
    Route::post('/lessons/{lessonId}/complete', [CourseProgressController::class, 'completeLesson']);

    // Submit course review
    Route::post('/courses/{courseId}/review', [CourseProgressController::class, 'submitReview']);
});
