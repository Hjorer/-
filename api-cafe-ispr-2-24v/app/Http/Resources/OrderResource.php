<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
{
    return [
        'id' => $this->id,
        'user_id' => $this->user_id,
        'status' => $this->status,
        'total_price' => (float) $this->total_price,
        'comment' => $this->comment,
        'created_at' => $this->created_at->toIso8601String(),
        'dishes' => $this->relationLoaded('dishes') ? $this->dishes->map(function ($dish) {
            return [
                'id' => $dish->id,
                'name' => $dish->name,
                'quantity' => $dish->pivot->quantity,
                'price_at_sale' => (float) $dish->pivot->price_at_sale,
            ];
        }) : null,
    ];
}

}
