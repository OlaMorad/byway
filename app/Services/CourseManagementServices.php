<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Models\Course;

class CourseManagementServices
{
    // عرض كل الكورسات
    public function getAllCourses()
    {
        $courses = Course::with(['user:id,name', 'category:id,name'])
            ->select('id', 'title', 'status', 'created_at', 'user_id', 'category_id')
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'status' => $course->status,
                    'created_at' => $course->created_at,
                    'instructor_name' => $course->user->name ?? null,
                    'category_name' => $course->category->name ?? null,
                ];
            });
        return ApiResponse::sendResponse(200, 'all Courses retrieved successfully', $courses);
    }
    // الموافقة على كورس
    public function approveCourse($courseId)
    {
        $course = Course::findOrFail($courseId);

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
        Course::where('id', $courseId)->delete();
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
            'created_at' => $course->created_at,

        ];
        return ApiResponse::sendResponse(200, 'Course updated successfully', $CourseData);
    }
}
