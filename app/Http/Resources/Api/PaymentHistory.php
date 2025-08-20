<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentHistory extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'items' => $this->items->map(function ($item) {
                return [
                    'course_id' => $item->course_id,
                    'course_name' => $item->course->title,
                    'price' => $item->price,
                ];
            }),

            'payment_method' => $this->user->paymentMethods->map(function ($method) {
                return [
                    'id' => $method->id,
                    'masked' => $method->masked,
                    'brand' => $method->brand, 
                ];
            }),
        ];
    }
}
