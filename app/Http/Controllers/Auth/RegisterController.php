<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Validation\ValidationException;
use App\Helpers\ApiResponse;
use App\Http\Requests\Api\RegisterRequest;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    public function register(RegisterRequest $request)
    {
        // Generate 6-digit verification code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'image' => 'storage/profile_images/default.png', // صورة افتراضية
            // 'headline' => $request->headline,
            // 'about' => $request->about,
            // 'twitter_link' => trim($request->twitter_link),
            // 'linkedin_link' => trim($request->linkedin_link),
            // 'youtube_link' => trim($request->youtube_link),
            // 'facebook_link' => trim($request->facebook_link),
            'verification_code' => $code,
            'is_verified' => false,
            'status' => 'active',
        ]);

        // Send verification email
        $this->sendVerificationEmail($user);

        return ApiResponse::sendResponse(201, "Registration successful. Check your email for the verification code.", [
            'user_id' => $user->id,
            'role' => $user->role,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'image' => asset($user->image),
            'bio' => $user->headline,
            'about' => $user->about,
            'twitter_link' => $request->twitter_link,
            'linkedin_link' => $request->linkedin_link,
            'youtube_link' => $request->youtube_link,
            'facebook_link' => $request->facebook_link,
            'status' => $user->status,
            'token' => $user->createToken('auth_token')->plainTextToken,
        ]);
    }

    /**
     * Send verification code to email.
     */
    protected function sendVerificationEmail($user)
    {
        try {
            Mail::send('emails.verification_code', ['user' => $user], function ($message) use ($user) {
                $message->from(config('mail.from.address'), config('mail.from.name'));
                $message->to($user->email)
                    ->subject('Verify Your Account');
            });

            Log::info("Verification code sent to user email: {$user->email}");
        } catch (\Exception $e) {
            Log::error("Failed to send verification code to {$user->email}: " . $e->getMessage());
        }
    }

    /**
     * Verify email with code.
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|size:6',
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        if ($user->verification_code !== $request->code) {
            return response()->json(['message' => 'Invalid verification code.'], 422);
        }

        $user->is_verified = true;
        $user->verification_code = null; // clear code
        $user->save();

        return response()->json([
            'message' => 'Email verified successfully. You can now log in.',
        ]);
    }

    /**
     * Redirect to Google OAuth.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Handle Google callback.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Google authentication failed.'], 401);
        }

        $user = User::updateOrCreate([
            'provider' => 'google',
            'provider_id' => $googleUser->getId(),
        ], [
            'name' => $googleUser->getName(),
            'email' => $googleUser->getEmail(),
            'avatar' => $googleUser->getAvatar(),
            'is_verified' => true,
            'role' => 'learner', // default; you can upgrade later
            'password' => $password ??= Hash::make(Str::random(24)), // required but not used
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Logged in with Google.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'avatar' => $user->avatar,
                'is_verified' => $user->is_verified,
            ]
        ]);
    }
}
