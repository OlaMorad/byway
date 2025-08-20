<?php

namespace App\Services;

use App\Models\User;
use App\Models\Course;
use App\Models\Review;
use App\Helpers\ApiResponse;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

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

    // دالة لإنشاء PDF واحد لكل التقارير
    public function generateFullPdfReport()
    {
        // استخرج البيانات من JsonResponse
        $generalStats = json_decode(json_encode($this->generalStatistics()->getData()->data), true);
        $coursesStats = json_decode(json_encode($this->coursesWithAvgRating()->getData()->data), true);

        // أنشئ PDF من البيانات
        $pdf = Pdf::loadView('reports', [
            'general' => $generalStats,
            'courses' => $coursesStats,
        ]);

        // جيب آخر رقم إذا مخزّن، أو بلش من 1
        $lastNumber = cache()->get('report_number', 0) + 1;

        // خزّن الرقم الجديد للمرة الجاية
        cache()->forever('report_number', $lastNumber);

        // اسم الملف
        $fileName = 'reports_' . $lastNumber . '.pdf';

        // خزنه بالـ storage/public/reports
        Storage::disk('public')->put('reports/' . $fileName, $pdf->output());
        $link = asset('storage/reports/' . $fileName);
        // رجّع الرابط فقط بالـ data
        return ApiResponse::sendResponse(200, 'Report as pdf generated successfully', $link);
    }
}
