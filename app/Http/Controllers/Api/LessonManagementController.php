<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Course;
use App\Http\Resources\Api\LessonResource;
use App\Http\Resources\Api\LessonCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class LessonManagementController extends Controller
{
    /**
     * عرض جميع دروس كورس معين
     */
    public function index($courseId)
    {
        // التأكد من أن المدرس يملك هذا الكورس
        $course = Course::where('user_id', Auth::id())->findOrFail($courseId);
        
        $lessons = $course->lessons()->orderBy('order')->get();

        return new LessonCollection($lessons);
    }

    /**
     * عرض درس معين
     */
    public function show($courseId, $lessonId)
    {
        // التأكد من أن المدرس يملك هذا الكورس
        $course = Course::where('user_id', Auth::id())->findOrFail($courseId);
        
        $lesson = $course->lessons()->findOrFail($lessonId);

        return new LessonResource($lesson);
    }

    /**
     * إنشاء درس جديد
     */
    public function store(Request $request, $courseId)
    {
        // التأكد من أن المدرس يملك هذا الكورس
        $course = Course::where('user_id', Auth::id())->findOrFail($courseId);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video' => 'required|file|mimes:mp4,avi,mov,wmv|max:102400', // 100MB max
            'video_duration' => 'nullable|integer|min:1',
            'materials' => 'nullable|array',
            'materials.*.name' => 'required_with:materials|string',
            'materials.*.type' => 'required_with:materials|in:pdf,link,document',
            'materials.*.url' => 'required_with:materials|string',
            'order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $lesson = new Lesson();
        $lesson->title = $request->title;
        $lesson->description = $request->description;
        $lesson->course_id = $courseId;
        $lesson->video_duration = $request->video_duration;
        $lesson->materials = $request->materials;
        $lesson->order = $request->order ?? $course->lessons()->count();

        // رفع فيديو الدرس
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('lessons/videos', 'public');
            $lesson->video_url = Storage::url($videoPath);
        }

        $lesson->save();

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الدرس بنجاح',
            'data' => new LessonResource($lesson)
        ], 201);
    }

    /**
     * تحديث درس موجود
     */
    public function update(Request $request, $courseId, $lessonId)
    {
        // التأكد من أن المدرس يملك هذا الكورس
        $course = Course::where('user_id', Auth::id())->findOrFail($courseId);
        
        $lesson = $course->lessons()->findOrFail($lessonId);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'video' => 'nullable|file|mimes:mp4,avi,mov,wmv|max:102400',
            'video_duration' => 'nullable|integer|min:1',
            'materials' => 'nullable|array',
            'materials.*.name' => 'required_with:materials|string',
            'materials.*.type' => 'required_with:materials|in:pdf,link,document',
            'materials.*.url' => 'required_with:materials|string',
            'order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $lesson->fill($request->only(['title', 'description', 'video_duration', 'materials', 'order']));

        // رفع فيديو جديد
        if ($request->hasFile('video')) {
            // حذف الفيديو القديم
            if ($lesson->video_url) {
                $oldVideoPath = str_replace('/storage/', '', $lesson->video_url);
                Storage::disk('public')->delete($oldVideoPath);
            }
            
            $videoPath = $request->file('video')->store('lessons/videos', 'public');
            $lesson->video_url = Storage::url($videoPath);
        }

        $lesson->save();

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الدرس بنجاح',
            'data' => new LessonResource($lesson)
        ]);
    }

    /**
     * حذف درس
     */
    public function destroy($courseId, $lessonId)
    {
        // التأكد من أن المدرس يملك هذا الكورس
        $course = Course::where('user_id', Auth::id())->findOrFail($courseId);
        
        $lesson = $course->lessons()->findOrFail($lessonId);

        // حذف فيديو الدرس
        if ($lesson->video_url) {
            $videoPath = str_replace('/storage/', '', $lesson->video_url);
            Storage::disk('public')->delete($videoPath);
        }

        $lesson->delete();

        // إعادة ترتيب الدروس المتبقية
        $this->reorderLessons($courseId);

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الدرس بنجاح'
        ]);
    }

    /**
     * تغيير ترتيب الدروس
     */
    public function reorder(Request $request, $courseId)
    {
        // التأكد من أن المدرس يملك هذا الكورس
        $course = Course::where('user_id', Auth::id())->findOrFail($courseId);

        $validator = Validator::make($request->all(), [
            'lesson_orders' => 'required|array',
            'lesson_orders.*.id' => 'required|exists:lessons,id',
            'lesson_orders.*.order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->lesson_orders as $item) {
            $lesson = $course->lessons()->find($item['id']);
            if ($lesson) {
                $lesson->order = $item['order'];
                $lesson->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تغيير ترتيب الدروس بنجاح'
        ]);
    }

    /**
     * إعادة ترتيب الدروس تلقائياً
     */
    private function reorderLessons($courseId)
    {
        $lessons = Lesson::where('course_id', $courseId)->orderBy('order')->get();
        
        foreach ($lessons as $index => $lesson) {
            $lesson->order = $index;
            $lesson->save();
        }
    }

    /**
     * رفع مواد إضافية للدرس
     */
    public function uploadMaterial(Request $request, $courseId, $lessonId)
    {
        // التأكد من أن المدرس يملك هذا الكورس
        $course = Course::where('user_id', Auth::id())->findOrFail($courseId);
        
        $lesson = $course->lessons()->findOrFail($lessonId);

        $validator = Validator::make($request->all(), [
            'material' => 'required|file|mimes:pdf,doc,docx,txt,zip,rar|max:10240', // 10MB max
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $file = $request->file('material');
        $filePath = $file->store('lessons/materials', 'public');
        $fileUrl = Storage::url($filePath);

        $materials = $lesson->materials ?? [];
        $materials[] = [
            'name' => $request->name,
            'type' => 'document',
            'url' => $fileUrl,
            'filename' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
        ];

        $lesson->materials = $materials;
        $lesson->save();

        return response()->json([
            'success' => true,
            'message' => 'تم رفع المادة الإضافية بنجاح',
            'data' => new LessonResource($lesson)
        ]);
    }
}
