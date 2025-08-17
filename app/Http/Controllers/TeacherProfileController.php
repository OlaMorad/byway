<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;

use App\Helpers\ApiResponse;
use App\Models\InstructorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeacherProfileController extends Controller
{
    //  show profile
    public function show()
    {
        $id= Auth::user()->id;

            $teacher = InstructorProfile::where('user_id',$id)->with('user')->get();

        if (!$teacher) {
            return ApiResponse::sendResponse(404, 'Teacher profile not found');
        }

        return ApiResponse::sendResponse(200, 'Teacher profile found successfully', $teacher);
    }

    // update profile//
    public function update(Request $request){
    $data = $request->validate([
        'bio'            => 'sometimes|string|max:1000',
        'name'=> 'sometimes|string|max:255',
        'total_earnings' => 'sometimes|numeric|min:0',
        'image'          => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'twitter_link'   => 'nullable|url',
        'linkdin_link'   => 'nullable|url',
        'youtube_link'   => 'nullable|url',
        'facebook_link'  => 'nullable|url'
    ]);

    // $instructor = InstructorProfile::where('user_id', Auth::id())->first();

    // if (!$instructor || $instructor->user_id !== auth()->id()) {
    //     return ApiResponse::sendResponse(403, 'Unauthorized to update this profile');
    // }

$user = auth()->user();
    $instructor = InstructorProfile::where('user_id', $user->id)->first();

    if (!$instructor || $instructor->user_id !== $user->id) {
        return ApiResponse::sendResponse(403, 'Unauthorized to update this profile');
    }

    // تحديث الاسم في جدول users إذا موجود
    if (isset($data['name'])) {
        $user->name = $data['name'];
        $user->save();
        unset($data['name']);

    }
    // لو فيه صورة جديدة
    if ($request->hasFile('image')) {
        if ($instructor->image && Storage::disk('public')->exists($instructor->image)) {
            Storage::disk('public')->delete($instructor->image);
        }
        $data['image'] = $request->file('image')->store('instructor', 'public');
    }
    // تحديث البيانات سواء فيه صورة أو لا

    $instructor->update($data);

    return ApiResponse::sendResponse(200, 'Profile updated successfully', $instructor);
}


    public function store(Request $request){
        $validated= $request->validate([
            'bio'            => 'required|string|max:1000',
            'name'           => 'required|string|max:255',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'twitter_link'   => 'nullable|url',
            'linkedin_link'  => 'nullable|url',
            'youtube_link'   => 'nullable|url',
            'facebook_link'  => 'nullable|url'

        ]);
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('instructors', 'public');
        }
        $validated['user_id'] =$request->user()->id();
        $profile = InstructorProfile::create($validated);
        return ApiResponse::sendResponse(200, 'Profile created successfully', $profile);

    }
}
