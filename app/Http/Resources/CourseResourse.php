<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResourse extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [

            "id" => $this->id,
            "user_id" =>    $this->user_id,
            "title" => $this->title,
            "description" => $this->description,
            'image_url' => $this->image_url ? asset('storage/' .$this->image_url) : null,
            'video_url' => $this->video_url ? asset('storage/' .$this->video_url) : null,
            "status" => $this->status,
            "price" => $this->price,
            "category_id" => $this->category_id,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
