<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class LessonCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->collection->count(),
                'course_id' => $this->collection->first()?->course_id,
                'total_duration' => $this->collection->sum('video_duration'),
                'formatted_total_duration' => $this->formatTotalDuration($this->collection->sum('video_duration')),
            ],
        ];
    }

    /**
    * Total Duration Format
    */
    private function formatTotalDuration(int $seconds): string
    {
        if ($seconds <= 0) {
            return 'Not specified';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        if ($hours > 0) {
            return sprintf('%d hours and %d minutes', $hours, $minutes);
        }

        return sprintf('%d minutes', $minutes);
    }
}
