<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Models\Course;
use App\Models\InstructorProfile;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserManagementServices
{
    // عرض كل المستخدمين المسجلين بالنظام
    public function allRegisteredUsers()
    {
        $users = User::where('role', '!=', 'admin')->get()->select('id', 'name', 'email', 'role', 'status', 'created_at');
        return ApiResponse::sendResponse(200, 'all users retrieved successfully', $users);
    }
    // عرض اليوزر بروفايل
    public function userProfile($userId)
    {
        $user = User::select('id', 'name', 'email', 'role', 'status', 'nationality', 'created_at')
            ->findOrFail($userId);


        // إذا كان المستخدم Instructor
        if ($user->role === 'instructor') {
            // عدد الكورسات
            $courseCount = Course::where('user_id', $user->id)->count();

            // // متوسط التقييم لكل الكورسات
            $averageRating = Review::whereHas('course', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->avg('rating');
            // جلب البايو والتوتال إيرنينغ من جدول instructor_profiles
            $instructorProfile = InstructorProfile::where('user_id', $user->id)
                ->select('bio', 'total_earnings')
                ->first();

            $user->course_count = $courseCount;
            $user->bio = $instructorProfile?->bio;
            $user->total_earnings = $instructorProfile?->total_earnings;
            $user->average_rating = round($averageRating, 2);
        }

        return ApiResponse::sendResponse(200, 'User profile retrieved successfully', $user);
    }
    // تغير حالة الحساب
    public function toggleUserStatus($userId)
    {
        $user = User::findOrFail($userId);
        $user->status = $user->status === 'Active' ? 'Blocked' : 'Active';
        $user->save();

        return ApiResponse::sendResponse(200, 'User status updated successfully to ' . $user->status);
    }
    // حذف حساب اليوزر
    public function deleteUser($userId)
    {
        User::findOrFail($userId)->delete();
        return ApiResponse::sendResponse(200, 'User account deleted successfully');
    }
    // البحث عن اليوزر
    public function searchUsers($key)
    {
        if (empty($key)) {
            return ApiResponse::sendResponse(400, 'Search key is required', []);
        }

        $users = User::search($key)->get();

        $filteredUsers = $users->where('role', '!=', 'admin')
            ->map(function ($user) {
                return [
                    'id'          => $user->id,
                    'name'        => $user->name,
                    'email'       => $user->email,
                    'image'       => $user->image,
                    'role'        => $user->role,
                    'status'      => $user->status,
                    'nationality' => $user->nationality,
                    'created_at'  => $user->created_at,
                ];
            })->values();

        return ApiResponse::sendResponse(200, 'Users search results retrieved successfully', $filteredUsers);
    }
    // تعديل حساب اليوزر
    public function UpdateUser($userId, array $data)
    {
        $user = User::findOrFail($userId);

        if (isset($data['name'])) {
            $user->name = $data['name'];
        }

        if (isset($data['role'])) {
            $user->role = $data['role'];
        }

        $user->save();
        // حصر الحقول المطلوبة في response
        $responseData = $user->only([
            'name',
            'email',
            'image',
            'role',
            'status',
            'nationality',
            'created_at'
        ]);
        return ApiResponse::sendResponse(200, 'User updated successfully', $responseData);
    }

    public function addInstructor(array $data)
    {

        // إنشاء يوزر جديد
        $user = User::create([
            'name'        => $data['name'],
            'email'       => $data['email'],
            'password'    => Hash::make($data['password']),
            'role'        => 'instructor',
            'status'      => 'Active',
            'nationality' => $data['nationality'] ?? null,
        ]);

        // إنشاء بروفايل إنستركتور
        $profile = InstructorProfile::create([
            'user_id'       => $user->id,
            'bio'           => $data['bio'] ?? null,
            'twitter_link'  => $data['twitter_link'] ?? null,
            'linkdin_link'  => $data['linkdin_link'] ?? null,
            'youtube_link'  => $data['youtube_link'] ?? null,
            'facebook_link' => $data['facebook_link'] ?? null,
        ]);

        $responseData = [
            'id'          => $user->id,
            'name'        => $user->name,
            'email'       => $user->email,
            'role'        => $user->role,
            'status'      => $user->status,
            'nationality' => $user->nationality,
            'bio' => $profile->bio,
            'twitter_link' => $profile->twitter_link,
            'linkdin_link' => $profile->linkdin_link,
            'youtube_link' => $profile->youtube_link,
            'facebook_link' => $profile->facebook_link,
        ];

        return ApiResponse::sendResponse(201, 'Instructor created successfully', $responseData);
    }

    public function updateInstructor(array $data, $id)
    {
        $user = User::with('instructorProfile')->findOrFail($id);

        // تعديل على جدول users
        if (isset($data['name'])) {
            $user->name = $data['name'];
        }
        if (isset($data['status'])) {
            $user->status = $data['status'];
        }
        $user->save();

        // تعديل على جدول instructor_profiles
        if ($user->instructorProfile) {
            $user->instructorProfile->update([
                'bio'           => $data['bio'] ?? $user->instructorProfile->bio,
                'twitter_link'  => $data['twitter_link'] ?? $user->instructorProfile->twitter_link,
                'linkdin_link'  => $data['linkdin_link'] ?? $user->instructorProfile->linkdin_link,
                'youtube_link'  => $data['youtube_link'] ?? $user->instructorProfile->youtube_link,
                'facebook_link' => $data['facebook_link'] ?? $user->instructorProfile->facebook_link,
            ]);
        }
        $profile = $user->instructorProfile;

        $responseData = [
            'id'          => $user->id,
            'name'        => $user->name,
            'email'       => $user->email,
            'role'        => $user->role,
            'status'      => $user->status,
            'nationality' => $user->nationality,
            'bio' => $profile->bio,
            'twitter_link' => $profile->twitter_link,
            'linkdin_link' => $profile->linkdin_link,
            'youtube_link' => $profile->youtube_link,
            'facebook_link' => $profile->facebook_link,
        ];
        return ApiResponse::sendResponse(200, 'Instructor updated successfully', $responseData);
    }

    public function allInstructors()
    {
        $instructors = User::with('instructorProfile')
            ->where('role', 'instructor')
            ->select('id', 'name', 'email', 'status', 'nationality', 'created_at')
            ->get();

        return ApiResponse::sendResponse(200, 'All instructors retrieved successfully', $instructors);
    }
    public function searchInstructors($key)
    {
        if (empty($key)) {
            return ApiResponse::sendResponse(400, 'Search key is required', []);
        }

        $users = User::search($key)->get();

        $filteredUsers = $users->where('role', 'instructor')
            ->map(function ($user) {
                // جلب البروفايل إنستركتور
                $profile = $user->instructorProfile;

                return [
                    'id'            => $user->id,
                    'name'          => $user->name,
                    'email'         => $user->email,
                    'role'          => $user->role,
                    'status'        => $user->status,
                    'nationality'   => $user->nationality,
                    'image'         => $user->image,
                    'bio'           => $profile?->bio,
                    'total_earnings' => $profile?->total_earnings,
                    'twitter_link'  => $profile?->twitter_link,
                    'linkdin_link'  => $profile?->linkdin_link,
                    'youtube_link'  => $profile?->youtube_link,
                    'facebook_link' => $profile?->facebook_link,
                    'created_at'    => $user->created_at,
                ];
            })->values();

        return ApiResponse::sendResponse(200, 'instructors search results retrieved successfully', $filteredUsers);
    }
}
