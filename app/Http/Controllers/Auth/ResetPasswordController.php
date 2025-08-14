<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ResetPasswordController extends Controller
{
    public function reset($request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
        'token' => 'required|string',
        'password' => 'required|min:6|confirmed',
    ]);

    $record = DB::table('password_resets')
        ->where('email', $request->email)
        ->first();

    if (! $record) {
        return ApiResponse::sendError('Invalid or expired token.', 400);
    }

    if (now()->greaterThan($record->expires_at)) {
        return ApiResponse::sendError('Token has expired.', 400);
    }

    if (!Hash::check($request->token, $record->token)) {
        return ApiResponse::sendError('Invalid token.', 400);
    }

    // ✅ Update password
    $user = User::where('email', $request->email)->first();
    $user->password = Hash::make($request->password);
    $user->save();

    // 🔐 Revoke all Sanctum tokens (log out from all devices)
    $user->tokens()->delete();

    // 🧹 Delete used token
    DB::table('password_resets')->where('email', $request->email)->delete();

    return ApiResponse::sendResponse(200, 'Password reset successfully.');
}
}
