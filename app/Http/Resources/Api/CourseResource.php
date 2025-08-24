<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
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
            'image_url' => $this->image_url,
            'video_url' => $this->video_url,
            'status' => $this->status,
            'status_text' => $this->status,
            'price' => $this->price,
            'formatted_price' => $this->formatted_price,
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
            'instructor' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'lessons_count' => $this->whenCounted('lessons', $this->lessons_count),
            'lessons' => LessonResource::collection($this->whenLoaded('lessons')),
            'reviews_count' => $this->whenCounted('reviews', $this->reviews_count),
            'average_rating' => $this->whenLoaded('reviews', function () {
                return $this->reviews->avg('rating') ?? 0;
            }),
            'enrollments_count' => $this->whenCounted('enrollments', $this->enrollments_count),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }


    /**
     * تنسيق السعر
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2) . ' $';
    }
}
