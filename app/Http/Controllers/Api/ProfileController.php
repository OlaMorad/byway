<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Helpers\ApiResponse;
use App\Http\Requests\APi\UpdateProfileRequest;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function __construct(
        protected AuthService $auth_service,

    ) {}
    /**
     * Get the authenticated learner's profile.
     */
    public function show(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'learner') {
            return ApiResponse::sendError('Unauthorized access.', 403);
        }

        return ApiResponse::sendResponse(200, 'Profile fetched successfully.', [
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'image' => $user->image,
                'headline' => $user->headline,
                'about' => $user->about,
                'twitter_link' => $user->twitter_link,
                'linkedin_link' => $user->linkedin_link,
                'youtube_link' => $user->youtube_link,
                'facebook_link' => $user->facebook_link,
            ]
        ]);
    }

    /**
     * Update learner profile.
     */
    public function update(UpdateProfileRequest $request)
    {
        $user = $this->auth_service->updateProfile($request->validated());

        return ApiResponse::sendResponse(200, 'Profile updated successfully.', [
            'user' => [
                'id'            => $user->id,
                'first_name'    => $user->first_name,
                'last_name'     => $user->last_name,
                'image' => asset($user->image) ? asset('storage/' .$user->image) : null,
                'bio'      => $user->bio,
                'about'         => $user->about,
                'nationality'   => $user->nationality,
                'twitter_link'  => $user->twitter_link,
                'linkedin_link' => $user->linkedin_link,
                'youtube_link'  => $user->youtube_link,
                'facebook_link' => $user->facebook_link,
            ]
        ]);
    }

    public function closeAccount(Request $request)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        $user = $request->user();

        // Check if already requested
        if ($user->deletion_requested_at) {
            return ApiResponse::sendError('Account closure already requested.', 400);
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return ApiResponse::sendError('Password is incorrect.', 400);
        }

        // Mark for deletion
        $user->update([
            'deletion_requested_at' => now()
        ]);

        return ApiResponse::sendResponse(200, 'Account closure requested. You can cancel within 14 days by logging in.');
    }

    /**
     * Get account status
     */
    public function status(Request $request)
    {
        $user = $request->user();
        $status = $user->deletion_requested_at ? 'pending_closure' : 'active';

        $data = ['status' => $status];

        if ($user->deletion_requested_at) {
            $data['closure_date'] = $user->deletion_requested_at->addDays(14)->toISOString();
        }

        return ApiResponse::sendResponse(200, 'Account status retrieved.', $data);
    }

    /**
     * عرض بروفايل الانستركتور حسب الـ ID
     */
    public function Show_Instructor_profile($instructorId)
    {
        $instructorData = $this->auth_service->getInstructorWithCourses($instructorId);

        if (!$instructorData) {
            return ApiResponse::sendError('Instructor not found.', 404);
        }

        return ApiResponse::sendResponse(200, 'Instructor profile fetched successfully.', $instructorData);
    }
}
