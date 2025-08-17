<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PaymentHistory;

class PaymentHistoryController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
           $orders = Order::with(['items.course'])
            ->where(['user_id' => $request->user()->id , 'status' => 'succeeded'])
            ->latest('created_at')
            ->get();

            if ($orders){
               return ApiResponse::sendResponse(200, 'Payment history retrieved successfully', PaymentHistory::collection($orders));
            }
            return ApiResponse::sendResponse(200, 'No payment history found for this user');
    }
}
