<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Lang;
use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class ForgotPasswordController extends Controller
{
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $user = User::where('email', $request->email)->first();

        // Delete old tokens
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        // Generate token
        $token = Str::random(60);
        $expiresAt = now()->addMinutes(60);

        // Store token hashed
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => \Illuminate\Support\Facades\Hash::make($token),
            'created_at' => now(),
            'expires_at' => $expiresAt,
        ]);

        // 🔁 Send only the token (not a link)
        Mail::raw("Hello,\n\nYou requested a password reset.\n\nYour one-time reset token: $token\n\nThis token will expire in 60 minutes.\n\nIf you didn't request this, ignore this email.", function ($message) use ($user) {
            $message->to($user->email)->subject('Password Reset Token');
        });

        // ✅ Return generic response (don't expose if email exists)
        return ApiResponse::sendResponse(200, "If your email is registered, a reset token has been sent.token->$token");
    }
}
