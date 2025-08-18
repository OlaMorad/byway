<?php

namespace App\Services;

use App\Models\User;
use App\Models\Course;
use App\Models\Review;
use App\Helpers\ApiResponse;
use App\Models\Payment;

class ReportsService
{
    // دالة الإحصائيات العامة
    public function generalStatistics()
    {
        $instructorsCount = User::where('role', 'instructor')->count();
        $learnersCount = User::where('role', 'learner')->count();
        $coursesCount = Course::count();
        $totalEarnings = Payment::sum('amount'); // مجموع المدفوعات

        $data = [
            'instructors' => $instructorsCount,
            'learners'    => $learnersCount,
            'courses'     => $coursesCount,
            'earnings'    => round($totalEarnings, 2),
        ];

        return ApiResponse::sendResponse(200, 'General statistics retrieved successfully', $data);
    }

    // دالة عرض كل الكورسات مع المتوسط التقييمات
    public function coursesWithAvgRating()
    {
        $courses = Course::with('reviews')
            ->get()
            ->map(function ($course) {
                $avgRating = $course->reviews->avg('rating') ?? 0;
                return [
                    'course_name' => $course->title,
                    'avg_rating'  => round($avgRating, 2),
                ];
            })
            ->sortByDesc('avg_rating') // ترتيب من الأعلى للأدنى
            ->values();

        return ApiResponse::sendResponse(200, 'Courses with average ratings retrieved successfully', $courses);
    }
}
