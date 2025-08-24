<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Review;
use App\Models\InstructorProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InstructorPublicController extends Controller
{
    /**
     * Get all public data for a specific instructor
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function show($id, Request $request): JsonResponse
    {
        try {
            // Get instructor user
            $instructor = User::where('id', $id)
                ->where('role', 'instructor')
                ->where('status', 'Active')
                ->first();

            if (!$instructor) {
                return response()->json([
                    'success' => false,
                    'message' => 'The teacher is not present or inactive.'
                ], 404);
            }

            // Get instructor profile
            $profile = $instructor->instructorProfile;

            // Get pagination parameters for courses
            $coursesPerPage = $request->get('courses_per_page', 10);
            $coursesPage = $request->get('courses_page', 1);
            
            // Validate courses pagination parameters
            if ($coursesPerPage < 1 || $coursesPerPage > 50) {
                $coursesPerPage = 10;
            }
            if ($coursesPage < 1) {
                $coursesPage = 1;
            }

            // Get instructor courses with pagination
            $courses = Course::where('user_id', $id)
                ->where('status', 'published')
                ->with(['category', 'lessons', 'reviews'])
                ->paginate($coursesPerPage, ['*'], 'courses_page', $coursesPage);

            $transformedCourses = $courses->getCollection()->map(function ($course) {
                $totalLessons = $course->lessons->count();
                $totalReviews = $course->reviews->count();
                $averageRating = $course->reviews->avg('rating') ?? 0;

                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'price' => $course->price,
                    'status' => $course->status,
                    'category' => $course->category ? [
                        'id' => $course->category->id,
                        'name' => $course->category->name
                    ] : null,
                    'total_lessons' => $totalLessons,
                    'total_reviews' => $totalReviews,
                    'average_rating' => round($averageRating, 1),
                    'created_at' => $course->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $course->updated_at->format('Y-m-d H:i:s')
                ];
            });

            // Get pagination parameters for reviews
            $reviewsPerPage = $request->get('reviews_per_page', 10);
            $reviewsPage = $request->get('reviews_page', 1);
            
            // Validate reviews pagination parameters
            if ($reviewsPerPage < 1 || $reviewsPerPage > 50) {
                $reviewsPerPage = 10;
            }
            if ($reviewsPage < 1) {
                $reviewsPage = 1;
            }

            // Get instructor reviews with pagination
            $reviews = Review::whereHas('course', function ($query) use ($id) {
                    $query->where('user_id', $id);
                })
                ->with(['user:id,name', 'course:id,title'])
                ->paginate($reviewsPerPage, ['*'], 'reviews_page', $reviewsPage);

            $transformedReviews = $reviews->getCollection()->map(function ($review) {
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'review' => $review->review,
                    'status' => $review->status,
                    'student' => [
                        'id' => $review->user->id,
                        'name' => $review->user->name
                    ],
                    'course' => [
                        'id' => $review->course->id,
                        'title' => $review->course->title
                    ],
                    'created_at' => $review->created_at->format('Y-m-d H:i:s')
                ];
            });

            // Calculate instructor statistics
            $totalCourses = $courses->total(); // Total count from pagination
            $totalStudents = $transformedCourses->sum(function ($course) {
                return $course['total_reviews']; // Using reviews as approximation for students
            });
            $averageRating = $transformedCourses->avg('average_rating') ?? 0;
            $totalLessons = $transformedCourses->sum('total_lessons');

            // Prepare response data
            $data = [
                'instructor' => [
                    'id' => $instructor->id,
                    'name' => $instructor->name,
                    'email' => $instructor->email,
                    'nationality' => $instructor->nationality,
                    'status' => $instructor->status,
                    'created_at' => $instructor->created_at->format('Y-m-d H:i:s')
                ],
                'profile' => $profile ? [
                    'bio' => $profile->bio,
                    'specialization' => $profile->specialization,
                    'experience_years' => $profile->experience_years,
                    'education' => $profile->education,
                    'certifications' => $profile->certifications,
                    'social_links' => [
                        'twitter' => $instructor->twitter_link,
                        'linkedin' => $instructor->linkedin_link,
                        'youtube' => $instructor->youtube_link,
                        'facebook' => $instructor->facebook_link
                    ]
                ] : null,
                'statistics' => [
                    'total_courses' => $totalCourses,
                    'total_students' => $totalStudents,
                    'total_lessons' => $totalLessons,
                    'average_rating' => round($averageRating, 1)
                ],
                'courses' => [
                    'data' => $transformedCourses,
                    'pagination' => [
                        'current_page' => $courses->currentPage(),
                        'last_page' => $courses->lastPage(),
                        'per_page' => $courses->perPage(),
                        'total' => $courses->total(),
                        'from' => $courses->firstItem(),
                        'to' => $courses->lastItem(),
                        'has_more_pages' => $courses->hasMorePages(),
                        'next_page_url' => $courses->nextPageUrl(),
                        'prev_page_url' => $courses->previousPageUrl(),
                    ]
                ],
                'reviews' => [
                    'data' => $transformedReviews,
                    'pagination' => [
                        'current_page' => $reviews->currentPage(),
                        'last_page' => $reviews->lastPage(),
                        'per_page' => $reviews->perPage(),
                        'total' => $reviews->total(),
                        'from' => $reviews->firstItem(),
                        'to' => $reviews->lastItem(),
                        'has_more_pages' => $reviews->hasMorePages(),
                        'next_page_url' => $reviews->nextPageUrl(),
                        'prev_page_url' => $reviews->previousPageUrl(),
                    ]
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Teacher data fetched successfully',
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching teacher data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all active instructors list with pagination
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10); // Default 10 items per page
            $page = $request->get('page', 1); // Default page 1
            
            // Validate pagination parameters
            if ($perPage < 1 || $perPage > 100) {
                $perPage = 10;
            }
            if ($page < 1) {
                $page = 1;
            }

            $instructors = User::where('role', 'instructor')
                ->where('status', 'Active')
                ->with(['instructorProfile'])
                ->paginate($perPage, ['*'], 'page', $page);

            // Transform the data
            $transformedData = $instructors->getCollection()->map(function ($instructor) {
                $profile = $instructor->instructorProfile;
                $coursesCount = Course::where('user_id', $instructor->id)
                    ->where('status', 'published')
                    ->count();

                $averageRating = Course::where('user_id', $instructor->id)
                    ->where('status', 'published')
                    ->with('reviews')
                    ->get()
                    ->avg(function ($course) {
                        return $course->reviews->avg('rating') ?? 0;
                    }) ?? 0;

                return [
                    'id' => $instructor->id,
                    'name' => $instructor->name,
                    'nationality' => $instructor->nationality,
                    'specialization' => $profile ? $profile->specialization : null,
                    'experience_years' => $profile ? $profile->experience_years : null,
                    'courses_count' => $coursesCount,
                    'average_rating' => round($averageRating, 1),
                    'created_at' => $instructor->created_at->format('Y-m-d H:i:s')
                ];
            });

            // Prepare pagination response
            $response = [
                'success' => true,
                'message' => 'تم جلب قائمة المدرسين بنجاح',
                'data' => $transformedData,
                'pagination' => [
                    'current_page' => $instructors->currentPage(),
                    'last_page' => $instructors->lastPage(),
                    'per_page' => $instructors->perPage(),
                    'total' => $instructors->total(),
                    'from' => $instructors->firstItem(),
                    'to' => $instructors->lastItem(),
                    'has_more_pages' => $instructors->hasMorePages(),
                    'next_page_url' => $instructors->nextPageUrl(),
                    'prev_page_url' => $instructors->previousPageUrl(),
                ]
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب قائمة المدرسين',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
