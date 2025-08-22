<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Models\User;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Setting;
use Carbon\Carbon;
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
            'Total Revenue' => (float) Payment::where('type', 'payment')->sum('amount'),
        ];
        return ApiResponse::sendResponse(200, 'Dashboard statistics retrieved successfully', $data);
    }

    public function getTopRatedCourses()
    {
        $courses = Course::withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->with('user:id,name')
            ->orderByDesc('reviews_avg_rating')
            ->take(5)
            ->get(['id', 'title', 'user_id'])
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'average_rating' => round($course->reviews_avg_rating ?? 0, 2),
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
                    'customer' => $payment->user->name ?? null,
                    'date'          => $payment->created_at->format('Y-m-d'),
                    'type'          => $payment->paymentMethod?->brand ?? null,
                    'amount'        => (float) $payment->amount,
                ];
            });

        return ApiResponse::sendResponse(200, 'Recent payments retrieved successfully', $payments);
    }


    public function getRevenueReport()
    {
        $currentYear = Carbon::now()->year;
        $startYear = $currentYear - 4 + 1; // آخر 3 سنين + السنة الحالية

        // جلب كل المدفوعات من النوع 'payment' ضمن السنوات المطلوبة
        $revenues = Payment::selectRaw('
            YEAR(created_at) as year,
            MONTH(created_at) as month,
            SUM(amount) as revenue
        ')
            ->where('type', 'payment')
            ->whereBetween(DB::raw('YEAR(created_at)'), [$startYear, $currentYear])
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'asc')
            ->get()
            ->keyBy(fn($item) => $item->year . '-' . $item->month);

        $result = [];

        for ($year = $startYear; $year <= $currentYear; $year++) {
            $result[$year] = [];

            for ($month = 1; $month <= 12; $month++) {
                $key = $year . '-' . $month;
                $revenue = isset($revenues[$key]) ? (float) $revenues[$key]->revenue : 0;

                $result[$year][] = [
                    'month' => date('M', mktime(0, 0, 0, $month, 1)),
                    'revenue' => $revenue,
                    'date' => $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT),
                ];
            }
        }

        // ترتيب السنوات من الأحدث للأقدم
        $result = collect($result)->sortKeysDesc();

        return ApiResponse::sendResponse(200, 'Revenue report retrieved successfully', $result);
    }


    public function getInstructorRevenueReport()
    {
        $currentYear = Carbon::now()->year;
        $startYear = $currentYear - 4 + 1; // آخر 3 سنين + السنة الحالية

        // نسبة العمولة من جدول settings أو القيمة الافتراضية 15%
        $commissionRate = Setting::first()->commission ?? 15.00;

        // جلب كل عمليات السحب (withdrawal) ضمن السنوات المطلوبة
        $withdrawals = Payment::selectRaw('
            YEAR(created_at) as year,
            MONTH(created_at) as month,
            SUM(amount) as total_withdrawals
        ')
            ->where('type', 'withdrawal')
            ->where('status', 'succeeded') // نتأكد انه Approved
            ->whereBetween(DB::raw('YEAR(created_at)'), [$startYear, $currentYear])
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'asc')
            ->get()
            ->keyBy(fn($item) => $item->year . '-' . $item->month);

        $result = [];

        for ($year = $startYear; $year <= $currentYear; $year++) {
            $result[$year] = [];

            for ($month = 1; $month <= 12; $month++) {
                $key = $year . '-' . $month;

                $withdrawalAmount = isset($withdrawals[$key]) ? (float) $withdrawals[$key]->total_withdrawals : 0;

                // خصم عمولة المنصة
                $instructorRevenue = $withdrawalAmount - (($withdrawalAmount * $commissionRate) / 100);

                $result[$year][] = [
                    'month' => date('M', mktime(0, 0, 0, $month, 1)),
                    'revenue' => round($instructorRevenue, 2),
                    'date' => $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT),
                ];
            }
        }

        // ترتيب السنوات من الأحدث للأقدم
        $result = collect($result)->sortKeysDesc();

        return ApiResponse::sendResponse(200, 'Instructor revenue report retrieved successfully', $result);
    }
}
