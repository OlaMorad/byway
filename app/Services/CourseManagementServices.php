<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Models\Course;

class CourseManagementServices
{
    // عرض كل الكورسات
    public function getAllCourses($filters = [])
    {
        $coursesQuery = Course::with(['user:id,name', 'category:id,name'])
            ->select('id', 'title', 'status', 'created_at', 'user_id', 'category_id');

        // فلترة حسب category_id
        if (!empty($filters['category_id'])) {
            $coursesQuery->where('category_id', $filters['category_id']);
        }

        // فلترة حسب status
        if (!empty($filters['status'])) {
            $coursesQuery->where('status', $filters['status']);
        }

        $courses = $coursesQuery->get()->map(function ($course) {
            return [
                'id' => $course->id,
                'title' => $course->title,
                'status' => $course->status,
                'created_at' => $course->created_at->format('Y:m:d'),
                'instructor_name' => $course->user->name ?? null,
                'category_name' => $course->category->name ?? null,
            ];
        });

        if ($courses->isEmpty()) {
            return ApiResponse::sendResponse(200, 'No courses found for the given filters');
        }

        return ApiResponse::sendResponse(200, 'Courses retrieved successfully', $courses);
    }

    // الموافقة على كورس
    public function approveCourse($courseId)
    {
        $course = Course::find($courseId);

        if (!$course) {
            return ApiResponse::sendResponse(404, 'Course not found');
        }

        if ($course->status === 'published') {
            return ApiResponse::sendResponse(200, 'Course is already published');
        }

        $course->status = 'published';
        $course->save();

        return ApiResponse::sendResponse(200, 'Course approved successfully');
    }

    // حذف كورس
    public function deleteCourse($courseId)
    {
        $course = Course::find($courseId);

        if (!$course) {
            return ApiResponse::sendResponse(404, 'Course not found');
        }

        $course->delete();
        return ApiResponse::sendResponse(200, 'Course deleted successfully');
    }

    // تعديل بيانات الكورس
    public function updateCourse($courseId, $data)
    {
        $course = Course::findOrFail($courseId);

        // تحديث البيانات المطلوبة
        $course->update([
            'title'       => $data['title'] ?? $course->title,
            'description' => $data['description'] ?? $course->description,
            'price'       => $data['price'] ?? $course->price,
            'category_id' => $data['category_id'] ?? $course->category_id,
        ]);
        $course->load(['user:id,name', 'category:id,name']);

        $CourseData = [
            'id' => $course->id,
            'title' => $course->title,
            'description' => $course->description,
            'video_url' => $course->video_url,
            'status' => $course->status,
            'price' => $course->price,
            'instructor_name' => $course->user->name ?? null,
            'category_name' => $course->category->name ?? null,
            'created_at' => $course->created_at->format('Y:m:d'),

        ];
        return ApiResponse::sendResponse(200, 'Course updated successfully', $CourseData);
    }

    public function getCourseById($courseId)
    {
        $course = Course::with([
            'user:id,name,email',
            'lessons:id,title,video_url,course_id',
            'reviews' => function ($q) {
                $q->latest()->take(5)->with('user:id,name');
            }
        ])->findOrFail($courseId);

        $avgRating = $course->reviews()->avg('rating');
        $reviewsCount = $course->reviews()->count();

        $courseData = [
            'id'               => $course->id,
            'title'            => $course->title,
            'description'      => $course->description,
            'video_url'        => $course->video_url,
            'instructor_name'  => $course->user->name ?? null,

            // الدروس
            'lessons' => $course->lessons->map(function ($lesson) {
                return [
                    'id'        => $lesson->id,
                    'title'     => $lesson->title,
                    'video_url' => $lesson->video_url,
                ];
            }),

            // الريفيوهات
            'average_rating'   => round($avgRating, 1),
            'reviews_count'    => $reviewsCount,
            'latest_reviews'   => $course->reviews->map(function ($review) {
                return [
                    'id'        => $review->id,
                    'review'    => $review->review,
                    'rating'    => $review->rating,
                    'user_name' => $review->user->name ?? null,
                    'created_at' => $review->created_at->format('Y:m:d'),
                ];
            }),
        ];

        return ApiResponse::sendResponse(200, 'Course details retrieved successfully', $courseData);
    }

    public function searchCourses($filters)
    {
        // إذا ما في فلتر → رجع كل الكورسات
        if (!$filters) {
            return $this->getAllCourses();
        }

        // بحث باستخدام لارافيل سكوت
        $courses = Course::search($filters)
            ->query(function ($query) {
                $query->with(['user:id,name', 'category:id,name']);
            })
            ->get()
            ->map(function ($course) {
                return [
                    'id'             => $course->id,
                    'title'          => $course->title,
                    'status'         => $course->status,
                    'created_at'     => $course->created_at->format('Y:m:d'),
                    'instructor_name' => $course->user->name ?? null,
                    'category_name'  => $course->category->name ?? null,
                ];
            });

        return ApiResponse::sendResponse(200, 'Filtered courses retrieved successfully', $courses);
    }
}
