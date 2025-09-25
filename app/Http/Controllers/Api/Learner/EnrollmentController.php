<?php

namespace App\Http\Controllers\Api\Learner;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $user = Auth::user();

            // جلب الكورسات يلي المستخدم مسجل فيها
            $enrollments = Enrollment::with(['course.instructor', 'course.lessons'])
                ->where('user_id', $user->id)
                ->paginate(15);

            // تجهيز البيانات للريسبونس
            $courses = $enrollments->map(function ($enrollment) {
                $course = $enrollment->course;

                return [
                    'course_id'          => $course->id,
                    'title'              => $course->title,
                    'course_image_url' => $course->image_url ? asset('storage/' . $course->image_url) : null,
                    'course_video_url' =>  $course->video_url ? asset('storage/' . $course->video_url) : null,
                    'instructor'         => $course->instructor
                        ? $course->instructor->first_name . ' ' . $course->instructor->last_name
                        : null,
                    'progress'           => $enrollment->progress . '%',
                    'total_lessons'      => $course->lessons->count(),
                    'completed_lessons'  => $enrollment->completed_lessons ?? 0,
                    'enrolled_at'        => $enrollment->created_at,
                    'enrollment_status'  => $enrollment->status,
                ];
            });

            return response()->json([
                'status'  => 200,
                'message' => 'Enrolled courses retrieved successfully.',
                'data'    => [
                    'courses'    => $courses,
                    'count'      => $enrollments->total(),
                    'pagination' => [
                        'current_page' => $enrollments->currentPage(),
                        'last_page'    => $enrollments->lastPage(),
                        'per_page'     => $enrollments->perPage(),
                        'total'        => $enrollments->total(),
                        'from'         => $enrollments->firstItem(),
                        'to'           => $enrollments->lastItem(),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Error retrieving enrolled courses.',
                'error'   => $e->getMessage(),
            ]);
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

            if (!$request->user() || $request->user()->role !== 'learner') {
                return ApiResponse::sendError('Unauthorized. Only learners can access this.', 403);
            }

            // Check enrollment
            $isEnrolled = Enrollment::where('learner_id', $userId)
                ->where('course_id', $courseId)
                ->exists();

            if (!$isEnrolled) {
                return ApiResponse::sendError('You are not enrolled in this course.', 403);
            }

            $course = Course::with([
                'instructor:id,first_name,last_name',
                'instructor.instructorProfile',
                'lessons:id,course_id,title,video_url,video_duration,order',
                'reviews.user:id,first_name,last_name', // reviews with learner name
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
                'image_url' => $course->image_url ? asset('storage/' . $course->image_url) : null,
                'video_url' => $course->video_url ? asset('storage/' . $course->video_url) : null,
                'instructor' => [
                    'id' => $course->instructor?->id ?? 0,
                    'name' => $course->instructor ? $course->instructor->fullName() : null,
                    'bio' => $course->instructor?->bio ?? 'No bio available',
                    'profile_photo' => $course->instructor?->image ? url('storage/' . $course->instructor->image) : null,
                ],
                'content' => $course->lessons->sortBy('order')->map(function ($lesson) {
                    return [
                        'id' => $lesson->id,
                        'title' => $lesson->title,
                        'duration' => $lesson->video_duration ?? 0,
                        'video_url' => $lesson->video_url ? url($lesson->video_url) : null,
                    ];
                }),
                'reviews' => $course->reviews->map(function ($review) {
                    return [
                        'rating' => $review->rating,
                        'user_image' => $review->user?->image,
                        'review' => $review->review,
                        'learner_name' => $review->user ? $review->user->fullName() : null,
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
