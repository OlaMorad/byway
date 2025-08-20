<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportsService;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function __construct(
        protected ReportsService $reportsService
    ) {}

    // الإحصائيات العامة
    public function generalStatistics()
    {
        return $this->reportsService->generalStatistics();
    }

    // الكورسات مع الافريج ريتينغ
    public function coursesAvgRating()
    {
        return $this->reportsService->coursesWithAvgRating();
    }
    
    public function downloadPdfReport()
    {
        return $this->reportsService->generateFullPdfReport();
    }
}
