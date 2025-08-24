<?php

namespace App\Http\Requests\Api;

use App\Models\Setting;
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
        $minWithdrawal = Setting::value('withdrawal') ?? 50.00;


        return [
            'payment_method' => 'required|string',
            'account_name'   => 'required|string',
            'account_number' => 'required_if:payment_method,bank|nullable|numeric',
            'bank_name'      => 'required_if:payment_method,bank|nullable|string',
            'amount'         => "required|numeric|min:{$minWithdrawal}",
            'email'          => 'required_unless:payment_method,bank|nullable|email',
        ];
    }
}
