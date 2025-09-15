<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class AuthService
{
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
            'twitter_link'  => $data['twitter_link']  ?? $user->twitter_link,
            'linkedin_link' => $data['linkedin_link'] ?? $user->linkedin_link,
            'youtube_link'  => $data['youtube_link']  ?? $user->youtube_link,
            'facebook_link' => $data['facebook_link'] ?? $user->facebook_link,
        ]);

        return $user;
    }
}
