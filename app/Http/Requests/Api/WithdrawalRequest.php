<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawalRequest extends FormRequest
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
            'payment_method' => 'required|string', 
            'account_name'   => 'required|string|min:3|max:100',
            'account_number' => 'required|numeric',
            'bank_name'      => 'required|string|min:3|max:100',
            'amount'         => 'required|numeric|min:50',

        ];
    }
}
