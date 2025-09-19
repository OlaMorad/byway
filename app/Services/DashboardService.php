<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Models\User;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
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
            ->with('user:id,first_name,last_name')
            ->orderByDesc('reviews_avg_rating')
            ->take(5)
            ->get(['id', 'title', 'user_id'])
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'average_rating' => round($course->reviews_avg_rating ?? 0, 2),
                    'reviews_count' => $course->reviews_count ?? 0,
                    'instructor_name' => $course->user ? $course->user->fullName() : null,
                ];
            });

        return ApiResponse::sendResponse(200, 'Top rated courses retrieved successfully', $courses);
    }

    public function getRecentPayments($limit = 10)
    {
        // جلب آخر المدفوعات مع علاقة اليوزر وطرق الدفع
        $payments = Payment::latest()
            ->take($limit)
            ->get()
            ->map(function ($payment) {
                $payload = is_array($payment->response_payload) ? $payment->response_payload : [];
                return [
                    'customer' => $payment->user ? $payment->user->fullName() : null,
                    'date'          => $payment->created_at->format('Y-m-d'),
                    'method'   => $payload['payment_method'] ?? null,
                    'amount'        => (float) $payment->amount,
                ];
            });

        return ApiResponse::sendResponse(200, 'Recent payments retrieved successfully', $payments);
    }


    public function getInstructorPayments($limit = 10)
    {
        $instructorId = Auth::id();

        $payments = Payment::whereHas('order.items.course', function ($query) use ($instructorId) {
            $query->where('user_id', $instructorId);
        })
            ->with(['user'])
            ->latest()
            ->take($limit)
            ->get()
            ->map(function ($payment) {
                $payload = is_array($payment->response_payload) ? $payment->response_payload : [];
                return [
                    'customer' => $payment->user->name ?? null,
                    'date'     => $payment->created_at->format('Y-m-d'),
                    'type' => $payload['payment_method'] ?? null,
                    'amount'   => (float) $payment->amount,
                ];
            });

        return ApiResponse::sendResponse(200, 'Instructor payments retrieved successfully', $payments);
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
        $instructorId = Auth::id(); // المستخدم الحالي

        // نسبة العمولة من جدول settings أو القيمة الافتراضية 15%
        $commissionRate = Setting::first()->commission ?? 15.00;

        // جلب كل عمليات السحب للمدرس الحالي
        $withdrawals = Payment::where('type', 'withdrawal')
            ->where('status', 'succeeded')
            ->where('user_id', $instructorId) // فقط السحوبات الخاصة بالمدرس
            ->whereBetween(DB::raw('YEAR(created_at)'), [$startYear, $currentYear])
            ->get()
            ->groupBy(function ($payment) {
                return $payment->created_at->format('Y-m');
            });

        $result = [];

        for ($year = $startYear; $year <= $currentYear; $year++) {
            $result[$year] = [];

            for ($month = 1; $month <= 12; $month++) {
                $key = sprintf('%d-%02d', $year, $month);

                $withdrawalAmount = isset($withdrawals[$key])
                    ? $withdrawals[$key]->sum('amount')
                    : 0;

                // خصم عمولة المنصة
                $instructorRevenue = $withdrawalAmount - (($withdrawalAmount * $commissionRate) / 100);

                $result[$year][] = [
                    'month' => date('M', mktime(0, 0, 0, $month, 1)),
                    'revenue' => round($instructorRevenue, 2),
                    'date' => $key,
                ];
            }
        }

        // ترتيب السنوات من الأحدث للأقدم
        $result = collect($result)->sortKeysDesc();

        return ApiResponse::sendResponse(200, 'Instructor revenue report retrieved successfully', $result);
    }
}
