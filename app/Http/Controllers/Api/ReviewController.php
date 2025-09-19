<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReviewInstructorServices;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        protected ReviewInstructorServices $review_instructor
    ) {}
    // جلب الرفيوهات الخاصة بكورسات الانستركتور الحالي
    public function instructorReviews()
    {
        return $this->review_instructor->getInstructorReviews();
    }
}
