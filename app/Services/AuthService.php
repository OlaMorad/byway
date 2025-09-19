<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    /**
     * جلب معلومات الانستركتور مع كورساته مع المتوسط وعدد الرفيوهات
     */
    public function getInstructorWithCourses($instructorId)
    {
        $instructor = User::where('id', $instructorId)->first();
        $instructorData = [
            'first_name'    => $instructor->first_name,
            'last_name'     => $instructor->last_name,
            'image'         => $instructor->image ? asset($instructor->image) : null,
            'headline'      => $instructor->headline,
            'about'         => $instructor->about,
            'twitter_link'  => $instructor->twitter_link,
            'linkedin_link' => $instructor->linkedin_link,
            'youtube_link'  => $instructor->youtube_link,
            'facebook_link' => $instructor->facebook_link,
        ];

        $courses = Course::withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('user_id', $instructor->id)
            ->orderByDesc('reviews_avg_rating')
            ->get()
            ->map(function ($course) {
                return [
                    'id'             => $course->id,
                    'title'          => $course->title,
                    'price'          => $course->price,
                    'image_url'      => $course->image_url ? asset($course->image_url) : null,
                    'reviews_count'  => $course->reviews_count,
                    'average_rating' => round($course->reviews_avg_rating ?? 0, 2),
                ];
            });
        $reviews = Review::with(['course:id,title', 'user:id,first_name,last_name'])
            ->whereHas('course', function ($query) use ($instructorId) {
                $query->where('user_id', $instructorId);
            })
            ->orderByDesc('created_at')
            ->paginate(10);

        $instructorData['courses'] = $courses;
        $instructorData['reviews'] = $reviews;

        return $instructorData;
    }
    // تعديل البروفايل سواء للانستركتور او الليرنر
    public function updateProfile(array $data)
    {
        $user = Auth::user();
        // // التعامل مع الصورة أولاً
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            // حفظ الصورة في مجلد public/profile_images
            $path = $data['image']->store('profile_images', 'public');
            $data['image'] = $path; // نخزن المسار في قاعدة البيانات
        }

        // تحديث جدول users بشكل اختياري
        $user->update([
            'first_name'    => $data['first_name']    ?? $user->first_name,
            'last_name'     => $data['last_name']     ?? $user->last_name,
            'image'         => $data['image']         ?? $user->image,
            'bio'      => $data['bio']      ?? $user->bio,
            'about'         => $data['about']         ?? $user->about,
            'nationality'   => $data['nationality']   ?? $user->nationality,
            'twitter_link'  => $data['twitter_link']  ?? $user->twitter_link,
            'linkedin_link' => $data['linkedin_link'] ?? $user->linkedin_link,
            'youtube_link'  => $data['youtube_link']  ?? $user->youtube_link,
            'facebook_link' => $data['facebook_link'] ?? $user->facebook_link,
        ]);

        return $user;
    }
}
