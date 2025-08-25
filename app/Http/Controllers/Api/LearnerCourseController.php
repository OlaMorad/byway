<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Helpers\ApiResponse;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Favorite;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Models\User;

class LearnerCourseController extends Controller
{
    /**
     * Get enrolled courses for the authenticated learner
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'per_page' => 'sometimes|integer|min:1|max:100',
                'page' => 'sometimes|integer|min:1'
            ]);

            $user = $request->user();

            // Check if user exists and has learner role
            if (!$user || $user->role !== 'learner') {
                return ApiResponse::sendError('Unauthorized. Only learners can access this.', 403);
            }

            // Get pagination parameters
            $perPage = $request->get('per_page', 15);
            $page = $request->get('page', 1);

            // Get favorite courses with eager loading to avoid N+1 problem
            $favoriteCourses = Favorite::with([
                'course.instructor:id,name',
                'course.lessons:id,course_id',
                'course.lessons.lessonCompletions' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }
            ])
            ->where('user_id', $user->id)
            ->paginate($perPage, ['*'], 'page', $page);

            // Transform the data
            $courses = $favoriteCourses->getCollection()->map(function ($favorite) use ($user) {
                $course = $favorite->course;
                
                if (!$course) {
                    return null; // Skip if course doesn't exist
                }

                $totalLessons = $course->lessons->count();
                $completedLessons = $course->lessons->sum(function ($lesson) {
                    return $lesson->lessonCompletions->count();
                });

                $progress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;

                return [
                    'course_id' => $course->id,
                    'title' => $course->title,
                    'instructor' => $course->instructor->name ?? 'Unknown',
                    'progress' => $progress . '%',
                    'total_lessons' => $totalLessons,
                    'completed_lessons' => $completedLessons,
                    'favorited_at' => $favorite->created_at?->toISOString(),
                ];
            })->filter(); // Remove null values

            // Prepare pagination data
            $paginationData = [
                'current_page' => $favoriteCourses->currentPage(),
                'last_page' => $favoriteCourses->lastPage(),
                'per_page' => $favoriteCourses->perPage(),
                'total' => $favoriteCourses->total(),
                'from' => $favoriteCourses->firstItem(),
                'to' => $favoriteCourses->lastItem(),
            ];

            return ApiResponse::sendResponse(200, 'Enrolled courses retrieved successfully.', [
                'courses' => $courses->values(),
                'count' => $courses->count(),
                'pagination' => $paginationData,
            ]);

        } catch (ValidationException $e) {
            return ApiResponse::sendError('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            Log::error('Error in LearnerCourseController@index: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'trace' => $e->getTraceAsString()
            ]);

            return ApiResponse::sendError('An error occurred while retrieving courses. Please try again later.', 500);
        }
    }

    /**
     * Get course progress details
     *
     * @param Request $request
     * @param int $courseId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCourseProgress(Request $request, $courseId)
    {
        try {
            $user = $request->user();

            if (!$user || $user->role !== 'learner') {
                return ApiResponse::sendError('Unauthorized. Only learners can access this.', 403);
            }

            // Validate course ID
            if (!is_numeric($courseId) || $courseId <= 0) {
                return ApiResponse::sendError('Invalid course ID', 400);
            }

            // Check if course exists and user has access
            $course = Course::with(['lessons', 'instructor:id,name'])
                ->where('id', $courseId)
                ->first();

            if (!$course) {
                return ApiResponse::sendError('Course not found', 404);
            }

            // Check if user has access to this course (either enrolled or favorited)
            $isEnrolled = Enrollment::where('learner_id', $user->id)
                ->where('course_id', $courseId)
                ->exists();



            if (!$isEnrolled) {
                return ApiResponse::sendError('You do not have access to this course. Please enroll or add to favorites first.', 403);
            }

            // Get lesson progress
            $lessons = $course->lessons->map(function ($lesson) use ($user) {
                $isCompleted = LessonCompletion::where('user_id', $user->id)
                    ->where('lesson_id', $lesson->id)
                    ->exists();

                return [
                    'lesson_id' => $lesson->id,
                    'title' => $lesson->title,
                    'is_completed' => $isCompleted,
                    'completed_at' => $isCompleted ? 
                        LessonCompletion::where('user_id', $user->id)
                            ->where('lesson_id', $lesson->id)
                            ->first()?->created_at?->toISOString() : null,
                ];
            });

            $totalLessons = $lessons->count();
            $completedLessons = $lessons->where('is_completed', true)->count();
            $progress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;

            return ApiResponse::sendResponse(200, 'Course progress retrieved successfully.', [
                'course' => [
                    'id' => $course->id,
                    'title' => $course->title,
                    'instructor' => $course->instructor->name ?? 'Unknown',
                ],
                'progress' => [
                    'percentage' => $progress . '%',
                    'total_lessons' => $totalLessons,
                    'completed_lessons' => $completedLessons,
                    'remaining_lessons' => $totalLessons - $completedLessons,
                ],
                'lessons' => $lessons,
            ]);

        } catch (\Exception $e) {
            Log::error('Error in LearnerCourseController@getCourseProgress: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'course_id' => $courseId,
                'trace' => $e->getTraceAsString()
            ]);

            return ApiResponse::sendError('An error occurred while retrieving course progress. Please try again later.', 500);
        }
    }
}
