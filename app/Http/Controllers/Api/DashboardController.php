<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService as ServicesDashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected ServicesDashboardService $dashboardService
    ) {}

    public function getDashboardStatistics()
    {
        return $this->dashboardService->getDashboardStatistics();
    }
    public function getTopRatedCourses()
    {
        return $this->dashboardService->getTopRatedCourses();
    }
    public function getRecentPayments($limit = 10)
    {
        return $this->dashboardService->getRecentPayments($limit);
    }
    public function getRevenueReport()
    {
        return $this->dashboardService->getRevenueReport();
    }
    //  المخطط عند ال Instructor
    public function getInstructorRevenueReport()
    {
        return $this->dashboardService->getInstructorRevenueReport();
    }

    public function getInstructorPayments($limit = 10)
    {
        return $this->dashboardService->getInstructorPayments($limit);
    }
}
