<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AddInstructorRequest extends FormRequest
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
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|string|min:6|confirmed',
            'nationality' => 'nullable|string|max:100',
            'bio'         => 'nullable|string|max:1000',
            'twitter_link' => 'nullable|url',
            'linkdin_link' => 'nullable|url',
            'youtube_link' => 'nullable|url',
            'facebook_link' => 'nullable|url',
        ];
    }
}
