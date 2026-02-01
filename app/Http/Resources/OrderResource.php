<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


/**
 * API Resource for Order.
 * 
 * Transforms Order model into consistent JSON response.
 * Benefits:
 * - Consistent response format
 * - Hide sensitive data
 * - Include computed attributes
 * - Easy to modify without changing controllers
 */
class OrderResource extends JsonResource
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
            'status' => $this->status,
            'total_amount' => $this->total_amount,
            'items_count' => $this->items->count(),
            'payments_count' => $this->payments->count(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),

            // Conditional relationships
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}