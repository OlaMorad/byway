<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Models\Course;
use App\Models\InstructorProfile;
use App\Models\Review;
use App\Models\User;

class UserManagementServices
{
    public function allRegisteredUsers()
    {
        $users = User::get()->select('id', 'name', 'email', 'role', 'status', 'created_at');
        return ApiResponse::sendResponse(200, 'all users retrieved successfully', $users);
    }
    public function userProfile($userId)
    {
        $user = User::select('id', 'name', 'email', 'role', 'status', 'nationality', 'created_at')
            ->findOrFail($userId);


        // إذا كان المستخدم Instructor
        if ($user->role === 'instructor') {
            // عدد الكورسات
            $courseCount = Course::where('user_id', $user->id)->count();

            // // متوسط التقييم لكل الكورسات
            // $averageRating = Review::whereHas('course', function ($q) use ($user) {
            //     $q->where('user-id', $user->id);
            // })->avg('rating');
            // جلب البايو والتوتال إيرنينغ من جدول instructor_profiles
            $instructorProfile = InstructorProfile::where('user_id', $user->id)
                ->select('bio', 'total_earnings')
                ->first();

            $user->course_count = $courseCount;
            $user->bio = $instructorProfile?->bio;
            $user->total_earnings = $instructorProfile?->total_earnings;
            // $user->average_rating = round($averageRating, 2);

        }

        return ApiResponse::sendResponse(200, 'User profile retrieved successfully', $user);
    }

    public function toggleUserStatus($userId)
    {
        $user = User::findOrFail($userId);
        $user->status = $user->status === 'Active' ? 'Blocked' : 'Active';
        $user->save();

        return ApiResponse::sendResponse(200, 'User status updated successfully', $user);
    }
    public function deleteUser($userId)
    {
        User::findOrFail($userId)->delete();
        return ApiResponse::sendResponse(200, 'User account deleted successfully');
    }
    // public function add_instructor(array $data)
    // {

    // }

    public function allInstructors()
    {
        $instructors = User::with('instructorProfile')
            ->where('role', 'instructor')
            ->select('id', 'name', 'email', 'status', 'nationality', 'created_at')
            ->get();

        return ApiResponse::sendResponse(200, 'All instructors retrieved successfully', $instructors);
    }
}
