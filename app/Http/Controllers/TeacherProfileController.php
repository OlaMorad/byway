<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\InstructorProfile;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherProfileController extends Controller
{

    public function show()
    {
        $userId = Auth::user()->id;
        $teacher = User::where('id', $userId)->first();

        if (!$teacher) {
            return ApiResponse::sendResponse(404, 'Teacher profile not found');
        }

        // رجع فقط الحقول المطلوبة
        $data = $teacher->only([
            'id',
            'first_name',
            'last_name',
            'email',
            'about',
            'bio',
            'twitter_link',
            'linkedin_link',
            'youtube_link',
            'facebook_link',
            'role',
            'status',
            'nationality',
        ]);

        // معالجة الصورة
        $data['image'] = $teacher->image
            ? asset('storage/' . $teacher->image)
            : null;
        //  التوب كورس حسب الريتنغ
        $topCourse = Course::where('user_id', $userId)
            ->withAvg('reviews', 'rating')
            // يحسب معدل الريتنغ
            ->withCount('reviews')
            ->orderByDesc('reviews_avg_rating')
            ->take(6)
            ->get()
            ->map(function ($course) {
                return [
                    'id'             => $course->id,
                    'title'          => $course->title,
                    'description'    => $course->description,
                    'price'          => (float) $course->price,
                    'image_url'      => $course->image_url ? asset('storage/' . $course->image_url) : null,
                    'avg_rating'     => round($course->reviews_avg_rating ?? 0, 2),
                    'reviews_count'  => $course->reviews_count,

                ];
            });

        $data['courses'] = $topCourse;

        // جميع كورسات المدرّس
        $courses = Course::where('user_id', $userId)->pluck('id');
        // إجمالي عدد الطلاب (enrollments)
        $totalStudents = Enrollment::whereIn('course_id', $courses)->count();
        // إجمالي عدد الريفيوهات
        $totalReviews = Review::whereIn('course_id', $courses)->count();

        // متوسط التقييم
        $averageRating = Review::whereIn('course_id', $courses)->avg('rating');

        // النسبة المئوية لكل تقييم من 1 لـ 5
        $ratingDistribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $count = Review::whereIn('course_id', $courses)->where('rating', $i)->count();
            $ratingDistribution[$i] = $totalReviews > 0 ? round(($count / $totalReviews) * 100, 2) : 0;
        }
        $data['total_students'] = $totalStudents;
        $data['total_reviews'] = $totalReviews;
        $data['average_rating'] = round($averageRating ?? 0, 2);
        $data['rating_distribution'] = $ratingDistribution;
        //  آخر 6 ريفيوهات
        $latestReviews = Review::with(['user:id,first_name,last_name,image', 'course:id,title'])
            ->whereHas('course', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get()
            ->map(function ($review) {
                return [
                    'id'             => $review->id,
                    'course'         => $review->course->title ?? null,
                    'reviewer'       => $review->user ? $review->user->fullName() : null,
                    'reviewer_image' => $review->user && $review->user->image ? asset('storage/' . $review->user->image) : null,
                    'rating'         => $review->rating,
                    'comment'        => $review->review,
                    'status'         => $review->status,
                    'date'           => $review->created_at->format('Y-m-d'),
                ];
            });

        $data['reviews'] = $latestReviews;

        return ApiResponse::sendResponse(200, 'Teacher profile found successfully', $data);
    }

    // update profile
    public function update(Request $request)
    {
        $data = $request->validate([
            'bio'            => 'sometimes|string|max:1000',
            'name'           => 'sometimes|string|max:255',
            'twitter_link'   => 'nullable|url',
            'linkdin_link'   => 'nullable|url',
            'youtube_link'   => 'nullable|url',
            'facebook_link'  => 'nullable|url',
        ]);

        $user = auth()->user();
        $instructor = InstructorProfile::where('user_id', $user->id)->first();

        if (!$instructor) {
            return ApiResponse::sendResponse(404, 'Profile not found');
        }

        if (array_key_exists('name', $data)) {
            $user->name = $data['name'];
            $user->save();
            unset($data['name']);
        }

        if (!empty($data)) {
            $instructor->update($data);
        }

        $instructor->load('user');

        return ApiResponse::sendResponse(200, 'Profile updated successfully', $instructor);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'bio'            => 'required|string|max:1000',
            'twitter_link'   => 'nullable|url',
            'linkdin_link'   => 'nullable|url',
            'youtube_link'   => 'nullable|url',
            'facebook_link'  => 'nullable|url',
        ]);

        $userId = auth()->id();

        $existing = InstructorProfile::where('user_id', $userId)->first();
        if ($existing) {
            return ApiResponse::sendResponse(409, 'Profile already exists', $existing);
        }

        $validated['user_id'] = $userId;

        $profile = InstructorProfile::create($validated)->load('user');

        return ApiResponse::sendResponse(201, 'Profile created successfully', $profile);
    }
}
