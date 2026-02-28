<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PackageTemplate */
class PackageTemplateResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'service_id' => $this->service_id,
            'service' => $this->whenLoaded('service', fn (): array => [
                'id' => $this->service->id,
                'name' => $this->service->name,
                'color' => $this->service->color,
            ]),
            'procedure_count' => $this->procedure_count,
            'price' => (float) $this->price,
            'duration_days' => $this->duration_days,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
