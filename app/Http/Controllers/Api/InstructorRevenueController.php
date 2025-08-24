<?php

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use App\Models\Setting;
use App\Models\OrderItem;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InstructorRevenueController extends Controller
{
    public function analytics(Request $request)
    {
        $instructor = $request->user();
        $totalSales = OrderItem::whereHas('course', function ($q) use ($instructor) {
            $q->where('user_id', $instructor->id);
        })
            ->sum('price');


        $commission = Setting::value('commission') ?? 15.00;
        $totalProfits = $totalSales * ((100 - $commission) / 100);


        $withdrawals = Payment::where('user_id', $instructor->id)
            ->where('status', 'succeeded')
            ->sum('amount');


        $availableBalance = $totalProfits - $withdrawals;

        $lastTransaction = Payment::where('user_id', $instructor->id)
            ->latest()
            ->first();

        return ApiResponse::sendResponse(200, 'Revenue analytics retrieved successfully', ['total_profits' => round($totalProfits), 'available_balance' => round($availableBalance), 'last_transaction' => round($lastTransaction->amount ?? null) 
        , 'minimum_withdrawal' => Setting::value('withdrawal') ?? 100]);
    }
}
