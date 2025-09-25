<?php

namespace App\Http\Requests\APi;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name'    => 'sometimes|string|max:255',
            'last_name'     => 'sometimes|string|max:255',
            'bio'      => 'sometimes|nullable|string|max:255',
            'about'         => 'sometimes|nullable|string|max:1000',
            'nationality'   => 'sometimes|nullable|string|max:255',
            'twitter_link' => [
                'nullable',
                'url',
                'regex:/^https?:\/\/(www\.)?(twitter\.com|x\.com)\/[a-zA-Z0-9_]+\/?$/'
            ],
            'linkedin_link' => ['nullable', 'url', 'regex:/^https?:\/\/(www\.)?linkedin\.com\/(in|company)\/[a-zA-Z0-9_-]+\/?$/'],
            'youtube_link' => [
                'nullable',
                'url',
                'regex:/^https?:\/\/(www\.)?youtube\.com\/.+$/'
            ],
            'facebook_link' => ['nullable', 'url', 'regex:/^https?:\/\/(www\.)?facebook\.com\/[a-zA-Z0-9\.]+\/?$/'],
            'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }
}
