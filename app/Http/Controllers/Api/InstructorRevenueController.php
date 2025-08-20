<?php

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use App\Models\OrderItem;
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

        $totalProfits = $totalSales * 0.85;     


        $withdrawals = Payment::where('user_id', $instructor->id)
            // ->where('type', 'withdrawal')
            ->where('status', 'success')
            ->sum('amount');


        $availableBalance = $totalProfits - $withdrawals;

        $lastTransaction = Payment::where('user_id', $instructor->id)
            ->latest()
            ->first();

      
        return response()->json([
            'total_profits' => round($totalProfits),
            'available_balance' => round($availableBalance),
            'last_transaction' => round($lastTransaction->amount ?? null),
            // 'monthly_revenue' => $monthlyRevenue,
        ]);
    }
}
