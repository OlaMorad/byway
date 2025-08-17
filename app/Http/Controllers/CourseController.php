<?php

namespace App\Http\Controllers;
use App\Models\Course;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class CourseController extends Controller
{public function store(Request $request)
{
    $validatecourses = $request->validate([
        'title'       => 'required|string|max:255',
        'description' => 'required|string',
        'video'       => 'required|file|mimes:mp4,mov,avi|max:204800', // 200MB
        'status'      => 'required|in:published,unpublished',
        'price'       => 'required|numeric|min:0',
        'category_id' => 'required|exists:categories,id'
    ]);

    // رفع الفيديو على Cloudinary
    $uploadedFileUrl = Cloudinary::upload(
        $request->file('video')->getRealPath(),
        [
            'folder'        => 'courses_videos',
            'resource_type' => 'video'
        ]
    )->getSecurePath();

    // تخزين بيانات الكورس
    $course = Course::create([
        'user_id'     => auth()->id(),
        'title'       => $request->title,
        'description' => $request->description,
        'price'       => $request->price,
        'category_id' => $request->category_id,
        'status'      => $request->status,
        'video_url'   => $uploadedFileUrl, // اللينك من Cloudinary
    ]);

    return ApiResponse::sendResponse(200, 'Course created successfully', $course);
}


    // show listcourses//
    public function listCourses(Request $request){
        $instructorid=Auth::id();
        $query=Course::where('user_id',$instructorid);


    //search by word//
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('title', 'LIKE', "%{$search}%")
            ->orWhere('description', 'LIKE', "%{$search}%");
        }


//filter by category//
            if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
//filter by date//

    if ($request->has('date')) {
        $query->whereDate('created_at', $request->date);
        }


             //  الترتيب
        if ($request->has('sort_by')) {
            $sortBy = $request->sort_by; // price | rating
            $sortOrder = $request->get('sort_order', 'asc'); // asc | desc
            $query->orderBy($sortBy, $sortOrder);
        }

        $courses = $query->paginate(10);

        return response()->json($courses);
    }

    //  تعديل الكورس
    public function update(Request $request, $id)
    {
        $course = Course::where('id', $id)
                        ->where('user_id', Auth::id())
                        ->firstOrFail();

        $course->update($request->only(['title', 'description', 'price', 'category_id']));

        return ApiResponse::sendResponse(200, 'Course updated successfully', $course);
    }

    //  حذف الكورس
    public function destroy($id)
    {
        $course = Course::where('id', $id)
                        ->where('user_id', Auth::id())
                        ->firstOrFail();

        $course->delete();

        return ApiResponse::sendResponse(200, 'Course deleted successfully', $course);
    }
}
