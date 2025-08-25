<?php

namespace App\Http\Controllers\Api\Learner;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class EnrollmentController extends Controller
{
    /**
     * Enroll learner in a course
     */
    public function enroll(Request $request, $courseId)
    {
        try {
            // Validate course ID
            if (!is_numeric($courseId) || $courseId <= 0) {
                return ApiResponse::sendError('Invalid course ID', 400);
            }

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
                'enrolled_at' => now(),
            ]);

            return ApiResponse::sendResponse(200, 'Successfully enrolled in the course.');

        } catch (\Exception $e) {
            Log::error('Error in EnrollmentController@enroll: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'course_id' => $courseId,
                'trace' => $e->getTraceAsString()
            ]);

            return ApiResponse::sendError('An error occurred while enrolling. Please try again later.', 500);
        }
    }

    /**
     * Get all courses the user is enrolled in
     */
    public function myCourses(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'per_page' => 'sometimes|integer|min:1|max:100',
                'page' => 'sometimes|integer|min:1'
            ]);

            $userId = $request->user()->id;

            // Get pagination parameters
            $perPage = $request->get('per_page', 15);
            $page = $request->get('page', 1);

            $courses = Course::whereHas('enrollments', function ($query) use ($userId) {
                $query->where('learner_id', $userId);
            })
                ->with(['instructor:id,name', 'reviews']) // instructor + reviews
                ->withAvg('reviews', 'rating')
                ->withCount('lessons')
                ->paginate($perPage, ['*'], 'page', $page);

            $transformedCourses = $courses->getCollection()->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'price' => $course->price,
                    'image' => $course->image,
                    'instructor' => $course->instructor ? $course->instructor->name : 'Unknown',
                    'rating' => round($course->reviews_avg_rating ?? 0, 1),
                    'lessons_count' => $course->lessons_count,
                    'enrolled_at' => $course->enrollments->first()->created_at->diffForHumans(),
                ];
            });

            // Prepare pagination data
            $paginationData = [
                'current_page' => $courses->currentPage(),
                'last_page' => $courses->lastPage(),
                'per_page' => $courses->perPage(),
                'total' => $courses->total(),
                'from' => $courses->firstItem(),
                'to' => $courses->lastItem(),
            ];

            return ApiResponse::sendResponse(200, 'My courses retrieved.', [
                'courses' => $transformedCourses,
                'pagination' => $paginationData,
            ]);

        } catch (ValidationException $e) {
            return ApiResponse::sendError('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            Log::error('Error in EnrollmentController@myCourses: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'trace' => $e->getTraceAsString()
            ]);

            return ApiResponse::sendError('An error occurred while retrieving courses. Please try again later.', 500);
        }
    }

    /**
     * View details of one enrolled course
     */
    public function showEnrolledCourse(Request $request, $courseId)
    {
        try {
            // Validate course ID
            if (!is_numeric($courseId) || $courseId <= 0) {
                return ApiResponse::sendError('Invalid course ID', 400);
            }

            $userId = $request->user()->id;

            // Check enrollment
            $isEnrolled = Enrollment::where('learner_id', $userId)
                ->where('course_id', $courseId)
                ->exists();

            if (!$isEnrolled) {
                return ApiResponse::sendError('You are not enrolled in this course.', 403);
            }

            $course = Course::with([
                'instructor:id,name',
                'instructor.instructorProfile',
                'lessons:id,course_id,title,video_url,video_duration,order',
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
                    'id' => $course->instructor?->id ?? 0,
                    'name' => $course->instructor?->name ?? 'Unknown Instructor',
                    'bio' => $course->instructor?->bio ?? 'No bio available',
                    'profile_photo' => $course->instructor?->profile_photo ? url('storage/' . $course->instructor->profile_photo) : null,
                ],
                'content' => $course->lessons->sortBy('order')->map(function ($lesson) {
                    return [
                        'id' => $lesson->id,
                        'title' => $lesson->title,
                        'duration' => $lesson->video_duration ?? 0,
                        'video_url' => $lesson->video_url,
                    ];
                }),
                'reviews' => $course->reviews->map(function ($review) {
                    return [
                        'rating' => $review->rating,
                        'review' => $review->review,
                        'learner_name' => $review->user?->name ?? 'Unknown User',
                        'created_at' => $review->created_at->diffForHumans(),
                    ];
                }),
                'enrollment_count' => $course->enrollments_count,
                'average_rating' => round($course->reviews_avg_rating ?? 0, 1),
            ]);

        } catch (\Exception $e) {
            Log::error('Error in EnrollmentController@showEnrolledCourse: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'course_id' => $courseId,
                'trace' => $e->getTraceAsString()
            ]);

            return ApiResponse::sendError('An error occurred while retrieving course details. Please try again later.', 500);
        }
    }
}
