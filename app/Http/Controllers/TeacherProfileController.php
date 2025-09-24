<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\InstructorProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherProfileController extends Controller
{

    public function show()
    {
        $user=Auth::user()->id;
        $teacher = User::where('id',$user)->first();

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
            ? asset($teacher->image)
            : null;

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
