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
        ])->where('status', 'published');

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

        return ApiResponse::sendResponse(200, 'Courses retrieved.', $courses);
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
            'image' => $course->image,
            'created_at' => $course->created_at,
            'updated_at' => $course->updated_at,

            'instructor' => [
                'id' => $course->user->id,
                'name' => $course->user->name,
                'about' => $course->user->about ?? 'No bio available',
                'image' => $course->user->image ? url('storage/' . $course->user->image) : null,
            ],

            'content' => $course->lessons->map(function ($lesson) {
                return [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
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


            'average_rating' => round($course->reviews->avg('rating'), 1),
        ]);
    }
}
