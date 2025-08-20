<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Models\User;
use App\Models\Course;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getDashboardStatistics()
    {
        $data = [
            'active_learners' => User::where([
                ['role', '=', 'learner'],
                ['status', '=', 'Active']
            ])->count(),

            'active_instructors' => User::where([
                ['role', '=', 'instructor'],
                ['status', '=', 'Active']
            ])->count(),

            'published_courses' => Course::where('status', 'published')->count(),
            'Total Revenue' => Payment::sum('amount'),
        ];
        return ApiResponse::sendResponse(200, 'Dashboard statistics retrieved successfully', $data);
    }

    public function getTopRatedCourses()
    {
        $courses = Course::withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->with('instructor:id,name')
            ->orderByDesc('reviews_avg_rating')
            ->take(5)
            ->get(['id', 'title', 'instructor_id'])
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'average_rating' => number_format($course->reviews_avg_rating ?? 0, 2),
                    'reviews_count' => $course->reviews_count ?? 0,
                    'instructor_name' => $course->user->name ?? null,
                ];
            });

        return ApiResponse::sendResponse(200, 'Top rated courses retrieved successfully', $courses);
    }

    public function getRecentPayments($limit = 10)
    {
        // جلب آخر المدفوعات مع علاقة اليوزر وطرق الدفع
        $payments = Payment::with('user.paymentMethods')
            ->latest()
            ->take($limit)
            ->get()
            ->map(function ($payment) {
                return [
                    'customer' => $payment->user->name,
                    'date'          => $payment->created_at->format('Y-m-d'),
                    'type'          => $payment->paymentMethod?->brand ?? null,
                    'amount'        => $payment->amount,
                ];
            });

        return ApiResponse::sendResponse(200, 'Recent payments retrieved successfully', $payments);
    }


    public function getRevenueReport()
    {
        $revenues = Payment::selectRaw('
            YEAR(created_at) as year,
            MONTH(created_at) as month,
            SUM(amount) as revenue
        ')
            ->whereBetween(DB::raw('YEAR(created_at)'), [2022, 2025]) // من 2022 لحد 2025
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'asc')
            ->get();

        // نعيد تنسيق البيانات
        $formatted = $revenues->map(function ($item) {
            $monthName = date('M', mktime(0, 0, 0, $item->month, 1));
            return [
                'month'   => $monthName,
                'revenue' => (float) $item->revenue,
                'date'    => $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT),
            ];
        });

        // تقسيم حسب السنوات (2025 → 2022)
        $grouped = $formatted->groupBy(function ($item) {
            return substr($item['date'], 0, 4); // السنة
        })->sortKeysDesc();

        return ApiResponse::sendResponse(200, 'Revenue report retrieved successfully', $grouped);
    }
}
