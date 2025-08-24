<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Models\Payment;
use App\Models\Setting;

class PaymentManagementService
{
    public function getPaymentStatistics()
    {
        // جلب نسبة العمولة أو القيمة الافتراضية 15%
        $commissionRate = Setting::first()->commission ?? 15.00;

        // جميع المدفوعات التي من النوع 'payment'
        $totalStudentPayments = Payment::where('type', 'payment')->sum('amount');

        //عدد عمليات السحب
        $totalWithdrawalsCount = Payment::where('type', 'withdrawal')->count();

        // ربح المنصة = المدفوعات * نسبة العمولة
        $platformEarnings = ($totalStudentPayments * $commissionRate) / 100;

        // أرباح المدرسين = المدفوعات - ربح المنصة
        $instructorEarnings = $totalStudentPayments - $platformEarnings;

        $data = [
            'platform_earnings' => round($platformEarnings, 2),
            'instructor_earnings' => round($instructorEarnings, 2),
            'total_withdrawals' => round($totalWithdrawalsCount, 2),
            'student_payments' => round($totalStudentPayments, 2),
        ];

        return ApiResponse::sendResponse(200, 'Payment statistics retrieved successfully', $data);
    }

    public function getAllPayments()
    {
        $payments = Payment::with(['user:id,name', 'userPaymentMethod'])
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        $payments->getCollection()->transform(function ($payment) {
            return [
                'id' => $payment->id,
                'date' => $payment->created_at->format('Y-m-d'),
                'user_name' => $payment->user?->name ?? null,
                'type' => $payment->type,
                'amount' => (float) $payment->amount,
                'status' => $payment->status,
                'payment_method' => $payment->userPaymentMethod?->brand ?? null, 
            ];
        });

        return ApiResponse::sendResponse(200, 'All payments retrieved successfully', $payments);
    }

    public function updateWithdrawalStatus(int $id, string $status)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return ApiResponse::sendResponse(404, 'Payment not found');
        }

        if ($payment->type !== 'withdrawal') {
            return ApiResponse::sendResponse(400, 'Only withdrawals can be updated');
        }

        if ($payment->status !== 'pending') {
            return ApiResponse::sendResponse(400, 'Only pending withdrawals can be updated');
        }

        $payment->status = $status;
        $payment->save();

        $message = $status === 'succeeded' ? 'Withdrawal approved successfully' : 'Withdrawal rejected successfully';

        return ApiResponse::sendResponse(200, $message, [
            'id' => $payment->id,
            'status' => $payment->status,
        ]);
    }


    public function getPaymentDetailsById(int $id)
    {
        $payment = Payment::with(['user', 'user.paymentMethods'])->find($id);

        if (!$payment) {
            return ApiResponse::sendResponse(404, 'Payment not found');
        }

        // لو النوع withdrawal
        if ($payment->type === 'withdrawal') {
            $data = [
                'instructor' => $payment->user?->name,
                'request_date' => $payment->created_at->format('Y-m-d'),
                'amount'       => (float) $payment->amount,
                'method'       => $payment->user?->paymentMethods->first()?->brand,
                'status'       => $payment->status,
            ];
        }

        // لو النوع payment
        elseif ($payment->type === 'payment') {

            // جيب أول كورس مرتبط مع اليوزر
            $course = $payment->user?->courses()->first();

            $data = [
                'student'      => $payment->user?->name,
                'payment_date' => $payment->created_at->format('Y-m-d'),
                'course'       => $course?->title, // لاحظ السؤال هون
                'method'       => $payment->user?->paymentMethods->first()?->brand,
                'amount'       => (float) $payment->amount,
                'status'       => $payment->status,
            ];
        } else {
            return ApiResponse::sendResponse(400, 'Unsupported payment type');
        }

        return ApiResponse::sendResponse(200, 'Payment details retrieved successfully', $data);
    }
}
