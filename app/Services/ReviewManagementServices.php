<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Models\Review;

class ReviewManagementServices
{
    public function getAllReview($instructorId = null)
    {
        // بناء الاستعلام الأساسي مع الـ relations
        $reviewsQuery = Review::with(['user:id,name', 'course:id,title,user_id']);

        // إذا تم تمرير instructorId، فلتر على أساس user_id للكورس
        if (!empty($instructorId)) {
            $reviewsQuery->whereHas('course', function ($query) use ($instructorId) {
                $query->where('user_id', $instructorId);
            });
        }

        $reviews = $reviewsQuery->get()->map(function ($review) {
            return [
                'course_name' => $review->course->title ?? null,
                'reviewer'    => $review->user->name ?? null,
                'rating'      => $review->rating,
                'comment'     => $review->review,
                'date'        => $review->created_at->format('Y-m-d'),
                'status'      => $review->status,
            ];
        });
        // تحقق إذا كانت المصفوفة فارغة عند وجود فلترة
        if (!empty($instructorId) && $reviews->isEmpty()) {
            return ApiResponse::sendResponse(200, 'No reviews found for this instructor');
        }
        return ApiResponse::sendResponse(200, 'Reviews retrieved successfully', $reviews);
    }

    public function showReview($reviewId)
    {
        $review = Review::with(['user:id,name', 'course:id,title'])
            ->findOrFail($reviewId);

        $reviewData = [
            'course'  => $review->course->title ?? null,
            'reviewer'     => $review->user->name ?? null,
            'comment'      => $review->review,
            'Date'   => $review->created_at->format('Y:m:d'),
        ];

        return ApiResponse::sendResponse(200, 'Review retrieved successfully', $reviewData);
    }

    public function deleteReview($reviewId)
    {
        $deleted = Review::where('id', $reviewId)->delete();

        if ($deleted) {
            return ApiResponse::sendResponse(200, 'Review deleted successfully');
        }

        return ApiResponse::sendResponse(404, 'Review not found');
    }
}
