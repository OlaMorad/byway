<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'video_url' => $this->video_url ? url($this->video_url) : null,
            'video_duration' => $this->video_duration,
            'formatted_duration' => $this->formatted_duration,
            'materials' => $this->materials_array,
            'materials_count' => count($this->materials_array),
            'order' => $this->order,
            'course_id' => $this->course_id,
            'course' => $this->whenLoaded('course', function () {
                return [
                    'id' => $this->course->id,
                    'title' => $this->course->title,
                ];
            }),
            'completion_status' => $this->when($request->user(), function () {
                $user = $request->user();
                if (!$user) return null;

                $completion = $this->lessonCompletions()
                    ->where('user_id', $user->id)
                    ->first();

                return $completion ? 'Completed' : 'Not Started';
            }),
            'completion_date' => $this->when($request->user(), function () {
                $user = $request->user();
                if (!$user) return null;

                $completion = $this->lessonCompletions()
                    ->where('user_id', $user->id)
                    ->first();

                return $completion?->created_at?->format('Y-m-d H:i:s');
            }),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * الحصول على المواد كأراي
     */
    public function getMaterialsArrayAttribute(): array
    {
        return $this->materials ?? [];
    }

    /**
     * العلاقة مع إكمالات الدروس
     */
    public function lessonCompletions()
    {
        return $this->hasMany(\App\Models\LessonCompletion::class);
    }
}
