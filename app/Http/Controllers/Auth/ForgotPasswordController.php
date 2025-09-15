<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Http\Requests\Api\ResetPasswordRequest;
use App\Http\Requests\Api\VerifyResetCodeRequest as ApiVerifyResetCodeRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        // حذف أي رموز قديمة
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        // توليد رمز عشوائي
        $token = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(60);

        // تخزين الرمز
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => $token,
            'created_at' => now(),
            'expires_at' => $expiresAt,
        ]);

        // Send the token to email
        Mail::send('emails.reset_token', ['token' => $token], function ($message) use ($user) {
            $message->to($user->email)->subject('Password Reset Code');
        });

        // Response message
        return ApiResponse::sendResponse(200, "A verification code has been sent to your email. Please check your inbox.");
    }

    public function verifyResetCode(ApiVerifyResetCodeRequest $request)
    {
        $data = $request->validated();

        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $data['email'])
            ->where('token', $data['code'])
            ->first();

        if (!$tokenRecord) {
            return ApiResponse::sendResponse(422, "Invalid email or verification code.");
        }

        if (now()->greaterThan($tokenRecord->expires_at)) {
            // حذف الرمز المنتهي
            DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
            return ApiResponse::sendResponse(422, "This verification code has expired.");
        }

        return ApiResponse::sendResponse(200, "Verification code is valid. You can now set a new password.");
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        // جلب بيانات الرمز مباشرة من الجدول
        $record = DB::table('password_reset_tokens')
            ->where('token', $request->code)
            ->first();

        if (!$record) {
            return ApiResponse::sendResponse(422, "Invalid verification code.");
        }

        // التحقق من انتهاء الصلاحية
        if (Carbon::parse($record->expires_at)->isPast()) {
            DB::table('password_reset_tokens')->where('token', $request->code)->delete();
            return ApiResponse::sendResponse(422, "Verification code has expired. Please request a new one.");
        }

        // الحصول على الإيميل المرتبط بالكود
        $user = User::where('email', $record->email)->first();

        if (!$user) {
            return ApiResponse::sendResponse(404, "email for this code not found.");
        }
        // التحقق إذا كانت الباسورد الجديدة نفس القديمة
        if (Hash::check($request->password, $user->password)) {
            return ApiResponse::sendResponse(422, "The new password cannot be the same as your old password.");
        }
        // تحديث كلمة المرور
        $user->password = Hash::make($request->password);
        $user->save();

        // حذف الكود بعد الاستخدام
        DB::table('password_reset_tokens')->where('token', $request->code)->delete();

        return ApiResponse::sendResponse(200, "Your password has been reset successfully. You can now log in.");
    }
}

