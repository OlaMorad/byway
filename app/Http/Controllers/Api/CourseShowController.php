<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Helpers\ApiResponse;


class CourseShowController extends Controller
{
    /**
     * List all published courses with optional filters
     */
    public function index(Request $request)
    {
        $query = Course::with([
            'user:id,name,image',
            'category:id,name',
        ])
        ->withCount(['lessons', 'reviews'])
        ->where('status', 'published');

        if ($request->has('search') && $request->search !== null && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('category_id') && $request->category_id !== null) {
            $query->where('category_id', $request->category_id);
        }

        $allowedSortBy = ['created_at', 'price', 'title'];
        $sortBy = in_array($request->get('sort_by'), $allowedSortBy, true) ? $request->get('sort_by') : 'created_at';
        $sortOrder = $request->get('sort_order', 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $perPage = (int) ($request->get('per_page', 12));
        if ($perPage <= 0) {
            $perPage = 12;
        }

        $courses = $query->paginate($perPage);

        // Transform courses to include image and video URLs with full paths
        $transformedCourses = $courses->getCollection()->map(function ($course) {
            return [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'price' => $course->price,
                'status' => $course->status,
                'image_url' => $course->image_url ? url('public/' . $course->image_url) : null,
                'video_url' => $course->video_url ? url('public/' . $course->video_url) : null,
                'lessons_count' => $course->lessons_count ?? 0,
                'reviews_count' => $course->reviews_count ?? 0,
                'category' => [
                    'id' => $course->category?->id,
                    'name' => $course->category?->name,
                ],
                'instructor' => [
                    'id' => $course->user?->id,
                    'name' => $course->user?->name,
                    'image' => $course->user?->image ? url('public/' . $course->user->image) : null,
                ],
                'created_at' => $course->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $course->updated_at?->format('Y-m-d H:i:s'),
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

        return ApiResponse::sendResponse(200, 'Courses retrieved.', [
            'courses' => $transformedCourses,
            'pagination' => $paginationData,
        ]);
    }

    /**
     * Get details of a specific course
     */
    public function show($id)
    {
        $course = Course::with([
            'user:id,name,about,image', // instructor
            'lessons:id,course_id,title,video_url',
            'reviews:user_id,course_id,rating,review,created_at',
            'reviews.user:id,name', // reviewer name
        ])->find($id);

        if (!$course) {
            return ApiResponse::sendError('Course not found.', 404);
        }

        // Format response
        return ApiResponse::sendResponse(200, 'Course details retrieved.', [
            'id' => $course->id,
            'title' => $course->title,
            'description' => $course->description,
            'price' => $course->price,
            'image_url' => $course->image_url ? url('public/' . $course->image_url) : null,
            'video_url' => $course->video_url ? url('public/' . $course->video_url) : null,
            'status' => $course->status,
            'created_at' => $course->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $course->updated_at?->format('Y-m-d H:i:s'),

            'instructor' => [
                'id' => $course->user?->id,
                'name' => $course->user?->name,
                'about' => $course->user?->about ?? 'No bio available',
                'image' => $course->user?->image ? url('public/' . $course->user->image) : null,
            ],

            'content' => $course->lessons->map(function ($lesson) {
                return [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                ];
            }),

            'reviews' => $course->reviews->map(function ($review) {
                return [
                    'rating' => $review->rating,
                    'review' => $review->review,
                    'learner_name' => $review->user?->name ?? 'Unknown',
                    'created_at' => $review->created_at->diffForHumans(),
                ];
            }),


            'average_rating' => round($course->reviews->avg('rating'), 1),
        ]);
    }
}
