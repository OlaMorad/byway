<?php

namespace App\Http\Controllers\Api\Learner;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * Enroll learner in a course
     */
    public function enroll(Request $request, $courseId)
    {
        $course = Course::find($courseId);

        if (!$course) {
            return ApiResponse::sendError('Course not found.', 404);
        }

        $userId = $request->user()->id;

        // Check if already enrolled
        $alreadyEnrolled = Enrollment::where('learner_id', $userId)
            ->where('course_id', $courseId)
            ->exists();

        if ($alreadyEnrolled) {
            return ApiResponse::sendResponse(200, 'Already enrolled in this course.');
        }

        // Enroll
        Enrollment::create([
            'learner_id' => $userId,
            'course_id' => $courseId,
        ]);

        return ApiResponse::sendResponse(200, 'Successfully enrolled in the course.');
    }

    /**
     * Get all courses the user is enrolled in
     */
    public function myCourses(Request $request)
    {
        $userId = $request->user()->id;

        $courses = Course::whereHas('enrollments', function ($query) use ($userId) {
            $query->where('learner_id', $userId);
        })
            ->with(['user:id,name', 'reviews']) // instructor + reviews
            ->withAvg('reviews', 'rating')
            ->withCount('lessons')
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'price' => $course->price,
                    'image' => $course->image,
                    'instructor' => $course->user ? $course->user->name : 'Unknown',
                    'rating' => round($course->reviews_avg_rating ?? 0, 1),
                    'lessons_count' => $course->lessons_count,
                    'enrolled_at' => $course->enrollments->first()->created_at->diffForHumans(),
                ];
            });

        return ApiResponse::sendResponse(200, 'My courses retrieved.', $courses);
    }

    /**
     * View details of one enrolled course
     */
    public function showEnrolledCourse(Request $request, $courseId)
    {
        $userId = $request->user()->id;

        // Check enrollment
        $isEnrolled = Enrollment::where('learner_id', $userId)
            ->where('course_id', $courseId)
            ->exists();

        if (!$isEnrolled) {
            return ApiResponse::sendError('You are not enrolled in this course.', 403);
        }

        $course = Course::with([
            'user:id,name',
            'user.instructorProfile',
            'lessons:id,course_id,title,video_url',
            'reviews.user:id,name', // reviews with learner name
        ])
            ->withAvg('reviews', 'rating')
            ->withCount('enrollments')
            ->find($courseId);

        if (!$course) {
            return ApiResponse::sendError('Course not found.', 404);
        }

        return ApiResponse::sendResponse(200, 'Course details retrieved.', [
            'id' => $course->id,
            'title' => $course->title,
            'description' => $course->description,
            'price' => $course->price,
            'image' => $course->image,
            'instructor' => [
                'id' => $course->user->id,
                'name' => $course->user->name,
                'bio' => $course->user->bio ?? 'No bio available',
                'profile_photo' => $course->user->profile_photo ? url('storage/' . $course->user->profile_photo) : null,
            ],
            'content' => $course->lessons->sortBy('sort_order')->map(function ($lesson) {
                return [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                    'duration' => $lesson->duration,
                    'video_url' => $lesson->video_url,
                ];
            }),
            'reviews' => $course->reviews->map(function ($review) {
                return [
                    'rating' => $review->rating,
                    'review' => $review->review,
                    'learner_name' => $review->user->name,
                    'created_at' => $review->created_at->diffForHumans(),
                ];
            }),
            'enrollment_count' => $course->enrollments_count,
            'average_rating' => round($course->reviews_avg_rating, 1),
        ]);
    }
}
