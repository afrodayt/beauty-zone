<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Payment */
class PaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'client' => $this->whenLoaded('client', fn (): array => [
                'id' => $this->client->id,
                'full_name' => $this->client->full_name,
                'phone' => $this->client->phone,
            ]),
            'visit_id' => $this->visit_id,
            'amount' => (float) $this->amount,
            'payment_method' => $this->payment_method->value,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'note' => $this->note,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
