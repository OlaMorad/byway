<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInstructorRequest extends FormRequest
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
            'name'        => 'sometimes|string|max:255',
            'bio'         => 'sometimes|string|max:1000',
            'twitter_link' => 'nullable|url',
            'linkdin_link' => 'nullable|url',
            'youtube_link' => 'nullable|url',
            'facebook_link' => 'nullable|url',
            'status'      => 'sometimes|in:Active,Blocked',
        ];
    }
}
