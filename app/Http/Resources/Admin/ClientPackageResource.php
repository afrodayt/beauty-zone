<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ClientPackage */
class ClientPackageResource extends JsonResource
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
            'package_template_id' => $this->package_template_id,
            'template' => $this->whenLoaded('template', fn (): array => [
                'id' => $this->template->id,
                'name' => $this->template->name,
            ]),
            'name' => $this->name,
            'total_procedures' => $this->total_procedures,
            'remaining_procedures' => $this->remaining_procedures,
            'purchased_amount' => (float) $this->purchased_amount,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'status' => $this->status->value,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
