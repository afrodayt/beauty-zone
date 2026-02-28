<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Client */
class ClientResource extends JsonResource
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
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'birth_date' => $this->birth_date?->toDateString(),
            'status' => $this->status->value,
            'notes' => $this->notes,
            'contraindications' => [
                'pregnancy' => $this->contra_pregnancy,
                'allergy' => $this->contra_allergy,
                'skin_damage' => $this->contra_skin_damage,
                'varicose' => $this->contra_varicose,
            ],
            'last_visit_at' => $this->last_visit_at?->toIso8601String(),
            'balance_eur' => round((float) ($this->balance_eur ?? 0), 2),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
