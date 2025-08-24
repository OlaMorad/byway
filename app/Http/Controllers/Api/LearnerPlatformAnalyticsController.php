<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Models\User;
use App\Models\Course;
use App\Models\Review;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LearnerPlatformAnalyticsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $course = Course::count();
        $review = Review::count();
        $learner = User::where('role', 'learner')->count();
        $instructor = User::where('role', 'instructor')->count();
        return ApiResponse::sendResponse(
            200,
            'Platform analytics retrieved successfully',
            [
                'courses'     => $course,
                'reviews'     => $review,
                'learners'    => $learner,
                'instructors' => $instructor,
            ]
        );
    }
}
