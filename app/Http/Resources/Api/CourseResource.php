<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\LessonResource;

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
            'image_url' => $this->image_url ? url($this->image_url) : null,
            'video_url' => $this->video_url ? url($this->video_url) : null,
            'status' => $this->status,
            'status_text' => $this->getStatusText(),
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
            'lessons' => $this->whenLoaded('lessons', function () {
                return LessonResource::collection($this->lessons);
            }),
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
     * Get status text in English
     */
    private function getStatusText(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'pending' => 'Pending Review',
            'published' => 'Published',
            default => 'Unknown'
        };
    }

    /**
     * Format price
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2) . ' $';
    }
}
