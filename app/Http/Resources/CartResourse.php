<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResourse extends JsonResource
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
            'user_id' => $this->user_id,
            'course_id' => $this->course_id,
            'instructor' => $this->course->user->name,
            'number_of_rating' =>$this->course->reviews->count('rating'),
            'rating' => min(5, round($this->course->reviews->avg('rating'))),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'course' => new CourseResourse($this->course),
            
        ];
    }
}
