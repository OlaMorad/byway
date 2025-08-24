<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TeacherProfileController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Api\CourseShowController;
use App\Http\Controllers\Api\WithdrawalController;
use App\Http\Controllers\Api\LearnerCourseController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\PaymentHistoryController;
use App\Http\Controllers\Api\UserManagementController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\CourseManagementController;
use App\Http\Controllers\Api\InstructorStripeController;
use App\Http\Controllers\Api\ReportsController;
use App\Http\Controllers\Api\ReviewManagementController;
use App\Http\Controllers\Api\InstructorRevenueController;
use App\Http\Controllers\Api\TeacherNotificationController;
use App\Http\Controllers\Api\Learner\CourseProgressController;
use App\Http\Controllers\Api\Learner\CourseInteractionController;
use App\Http\Controllers\Api\Learner\NotificationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PlatformSettingsController;
use App\Http\Controllers\Api\InstructorPublicController;
use App\Http\Controllers\Api\Learner\EnrollmentController;

// =====================================================================
// Public Instructor Routes (No Authentication Required)
// =====================================================================
Route::get('/all-instructors', [InstructorPublicController::class, 'index']);
Route::get('/all-instructors/{id}', [InstructorPublicController::class, 'show']);

// =====================================================================
// Auth & User
// =====================================================================
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/verify-code', [RegisterController::class, 'verifyCode']);
Route::post('/login', [LoginController::class, 'login']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', fn(Request $request) => $request->user());
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/close-account', [ProfileController::class, 'closeAccount']);
    Route::get('/profile/status', [ProfileController::class, 'status']);
});

// =====================================================================
// Social Login
// =====================================================================
Route::get('/auth/google/redirect', [RegisterController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [RegisterController::class, 'handleGoogleCallback']);

// =====================================================================
// Instructor Profile
// =====================================================================
Route::middleware(['auth:sanctum', 'role:instructor'])->group(function () {
    Route::get('/instructor/profile', [TeacherProfileController::class, 'show']);
    Route::patch('/instructor/profile/update', [TeacherProfileController::class, 'update']);
    Route::post('/instructor/profile/store', [TeacherProfileController::class, 'store']);
});

// =====================================================================
// Courses
// =====================================================================
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/courses', [CourseController::class, 'store']);
    Route::get('/instructor/courses', [CourseController::class, 'listCourses'])->middleware('role:instructor');
    Route::get('/instructor/courses/{id}', [CourseController::class, 'show'])->middleware('role:instructor');
    Route::post('/instructor/courses/{id}', [CourseController::class, 'update'])->middleware('role:instructor');
    Route::delete('/instructor/courses/{id}', [CourseController::class, 'destroy'])->middleware('role:instructor');
});


// =====================================================================
// Admin Routes
// =====================================================================

Route::middleware(['auth:sanctum', 'role:instructor'])->group(function () {
    Route::get('/instructor/revenue-report', [DashboardController::class, 'getInstructorRevenueReport']);
    Route::get('/instructor/payments', [DashboardController::class, 'getInstructorPayments']);
});

// =====================================================================
// Dashboard
// =====================================================================
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/statistics', [DashboardController::class, 'getDashboardStatistics']);
        Route::get('/top-rated-courses', [DashboardController::class, 'getTopRatedCourses']);
        Route::get('/recent-payments', [DashboardController::class, 'getRecentPayments']);
        Route::get('/revenue-report', [DashboardController::class, 'getRevenueReport']);
    });

    // User Management
    Route::prefix('users')->group(function () {
        Route::get('/', [UserManagementController::class, 'index']);
        Route::get('/search', [UserManagementController::class, 'searchUsers']);
        Route::get('/{id}', [UserManagementController::class, 'show']);
        Route::patch('/toggle-status/{id}', [UserManagementController::class, 'toggleStatus']);
        Route::patch('/{userId}', [UserManagementController::class, 'updateUser']);
        Route::delete('/{id}', [UserManagementController::class, 'destroy']);
        Route::post('/add/admin', [UserManagementController::class, 'addAdmin']);
    });

    // Instructors
    Route::get('/instructors', [UserManagementController::class, 'allInstructors']);
    Route::post('/instructors', [UserManagementController::class, 'addInstructor']);
    Route::put('/instructors/{id}', [UserManagementController::class, 'updateInstructorProfile']);
    Route::get('/instructors/search', [UserManagementController::class, 'searchInstructors']);

    // Courses Management
    Route::prefix('courses')->group(function () {
        Route::get('/', [CourseManagementController::class, 'index']);
        Route::get('/{id}', [CourseManagementController::class, 'show']);
        Route::put('/{courseId}', [CourseManagementController::class, 'update']);
        Route::delete('/{courseId}', [CourseManagementController::class, 'destroy']);
        Route::patch('/approve/{id}', [CourseManagementController::class, 'approve']);
        Route::get('/search', [CourseManagementController::class, 'search']);
    });

    // Reviews
    Route::prefix('reviews')->group(function () {
        Route::get('/', [ReviewManagementController::class, 'index']);
        Route::get('/{id}', [ReviewManagementController::class, 'show']);
        Route::delete('/{id}', [ReviewManagementController::class, 'destroy']);
        Route::get('/search', [ReviewManagementController::class, 'search']);
    });

    // Categories
    Route::prefix('categories')->group(function () {
        Route::get('/', [PlatformSettingsController::class, 'index']);
        Route::post('/', [PlatformSettingsController::class, 'store']);
        Route::put('/{id}', [PlatformSettingsController::class, 'update']);
        Route::delete('/{id}', [PlatformSettingsController::class, 'destroy']);
    });

    // Platform Settings
    Route::prefix('settings')->group(function () {
        Route::get('/', [PlatformSettingsController::class, 'showSettings']);
        Route::put('/', [PlatformSettingsController::class, 'editSettings']);
    });

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportsController::class, 'generalStatistics']);
        Route::get('/courses', [ReportsController::class, 'coursesAvgRating']);
        Route::get('/download', [ReportsController::class, 'downloadPdfReport']);
    });

    // Payment
    Route::prefix('payments')->group(function () {
        Route::get('/statistics', [PaymentController::class, 'statistics']);        // إحصائيات الدفعات
        Route::get('/all', [PaymentController::class, 'allPayments']);             // كل الدفعات
        Route::patch('/withdrawals/approve/{id}', [PaymentController::class, 'approveWithdrawal']); // الموافقة على سحب
        Route::patch('/withdrawals/reject/{id}', [PaymentController::class, 'rejectWithdrawal']);   // رفض سحب
        Route::get('/{id}', [PaymentController::class, 'show']);                   // تفاصيل دفع معينة
    });

});


// =====================================================================
// Cart
// =====================================================================
Route::middleware('auth:sanctum')->controller(CartController::class)->group(function () {
    Route::get('/cart', 'index');
    Route::post('/cart', 'add');
    Route::delete('/cart/{course}', 'remove');
});

// =====================================================================
// Payment
// =====================================================================
Route::middleware('auth:sanctum')->group(function () {
    Route::post('payment-methods/setup-intent', [PaymentMethodController::class, 'createSetupIntent']);
    Route::apiResource('payment-methods', PaymentMethodController::class)->except(['show', 'update']);
    Route::post('/checkout', [CheckoutController::class, 'checkout']);
    Route::post('/checkout/confirm', [CheckoutController::class, 'confirmWithSavedPM']);
    Route::get('/payment-history', PaymentHistoryController::class);
    Route::post('/instructor/withdrawals/request', [WithdrawalController::class, 'requestWithdrawal']);
});


Route::get('/instructor/revenue-analytics', [InstructorRevenueController::class, 'analytics'])->middleware('auth:sanctum');

// =====================================================================
// Notifications
// =====================================================================
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/teacher/notifications', [TeacherNotificationController::class, 'index']);
    Route::post('/teacher/notifications/mark-all', [TeacherNotificationController::class, 'markAllAsRead']);
    Route::post('/teacher/notifications/{id}/read', [TeacherNotificationController::class, 'markAsRead']);
    Route::delete('/teacher/notifications/{id}', [TeacherNotificationController::class, 'destroy']);
    Route::delete('/teacher/notifications', [TeacherNotificationController::class, 'destroyAll']);
});

// =====================================================================
// Learner
// =====================================================================
Route::middleware('auth:sanctum')->prefix('learner')->group(function () {
    // Courses
    Route::get('/courses', [LearnerCourseController::class, 'index']);

    // Favorites
    Route::post('/favorites/add', [CourseInteractionController::class, 'addToFavorites']);
    Route::post('/favorites/remove', [CourseInteractionController::class, 'removeFromFavorites']);
    Route::get('/favorites', [CourseInteractionController::class, 'getFavorites']);

    // Cart
    Route::post('/cart/add', [CourseInteractionController::class, 'addToCart']);
    Route::post('/cart/remove', [CourseInteractionController::class, 'removeFromCart']);
    Route::get('/cart', [CourseInteractionController::class, 'getCart']);

    // Lessons & Reviews
    Route::post('/lessons/{lessonId}/complete', [CourseProgressController::class, 'completeLesson']);
    Route::post('/courses/{courseId}/review', [CourseProgressController::class, 'submitReview']);
});



Route::get('/all-courses', [CourseShowController::class, 'index']);
Route::get('/course/{id}', [CourseShowController::class, 'show']);


Route::middleware('auth:sanctum')->prefix('learner')->group(function () {
    // Enroll in course
    Route::post('/courses/{courseId}/enroll', [EnrollmentController::class, 'enroll']);

    // My Courses
    Route::get('/my-courses', [EnrollmentController::class, 'myCourses']);

    // View one enrolled course
    Route::get('/courses/{courseId}', [EnrollmentController::class, 'showEnrolledCourse']);
});
