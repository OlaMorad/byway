<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentManagementService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentManagementService $paymentManagement
    ) {}

    public function statistics()
    {
        return $this->paymentManagement->getPaymentStatistics();
    }

    public function allPayments()
    {
        return $this->paymentManagement->getAllPayments();
    }

    public function approveWithdrawal(int $id)
    {
        return $this->paymentManagement->updateWithdrawalStatus($id, 'succeeded');
    }

    public function rejectWithdrawal(int $id)
    {
        return $this->paymentManagement->updateWithdrawalStatus($id, 'failed');
    }

    public function show(int $id)
    {
        return $this->paymentManagement->getPaymentDetailsById($id);
    }
}
