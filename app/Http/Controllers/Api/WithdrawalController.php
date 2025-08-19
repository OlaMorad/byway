<?php

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use App\Models\Setting;
use App\Models\OrderItem;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\WithdrawalRequest;

class WithdrawalController extends Controller
{
    public function requestWithdrawal(WithdrawalRequest $request)
{
    $dataValidated = $request->validated();
    $user = $request->user();

    $totalProfits = OrderItem::whereHas('course', function ($q) use ($user) {
        $q->where('user_id', $user->id);
    })->sum('price');

    $commission = Setting::value('commission') ?? 15.00;   
    $netProfits = $totalProfits * ((100 - $commission) / 100);

    $withdrawals = Payment::where('user_id', $user->id)
        ->where('type', 'withdrawal')
        ->where('status', 'success')
        ->sum('amount');

    $availableBalance = $netProfits - $withdrawals;

    if ($dataValidated['amount'] > $availableBalance) {
        return response()->json(['error' => 'Requested amount exceeds available balance'], 422);
    }

    $payment = Payment::create([
        'user_id' => $user->id,
        'type'    => 'withdrawal',
        'status'  => 'pending',
        'amount'  => $dataValidated['amount'],
        'currency'=> 'usd',
        'response_payload' => json_encode($dataValidated),
    ]);


    return ApiResponse::sendResponse(200, 'Withdrawal request submitted successfully');
}

}
