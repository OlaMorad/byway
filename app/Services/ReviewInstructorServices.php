<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Helpers\ApiResponse;
use App\Models\Course;
use App\Models\Review;

class ReviewInstructorServices
{
    public function getInstructorReviews()
    {
        $instructorId = Auth::id();

        // جلب الكورسات الخاصة بالإنستركتور
        $courses = Course::where('user_id', $instructorId)->pluck('id');

        if ($courses->isEmpty()) {
            return ApiResponse::sendResponse(200, 'No courses found for this instructor');
        }

        // عدد العناصر بالصفحة (إما من البارامز أو افتراضي 10)
        $perPage = request()->get('per_page', 10);

        // جلب الريفيوهات مع الباجنيشن
        $reviews = Review::with(['user:id,name,image', 'course:id,title'])
            ->whereIn('course_id', $courses)
            ->orderBy('created_at', 'desc') // ترتيب من الأحدث للأقدم
            ->paginate($perPage)
            ->through(function ($review) {
                return [
                    'id'             => $review->id,
                    'course_name'    => $review->course->title ?? null,
                    'reviewer'       => $review->user->name ?? null,
                    //        'reviewer_image' => $review->user->image ? asset('storage/' . $review->user->image) : null,
                    'rating'         => $review->rating,
                    'comment'        => $review->review,
                    'date'           => $review->created_at->format('Y-m-d'),
                    'status'         => $review->status,
                ];
            });

        if ($reviews->isEmpty()) {
            return ApiResponse::sendResponse(200, 'No reviews found for your courses');
        }

        return ApiResponse::sendResponse(200, 'Reviews retrieved successfully', $reviews);
    }
}
