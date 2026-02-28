<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Visit */
class VisitResource extends JsonResource
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
            'service_id' => $this->service_id,
            'service' => $this->whenLoaded('service', fn (): array => [
                'id' => $this->service->id,
                'name' => $this->service->name,
                'color' => $this->service->color,
                'duration_minutes' => $this->service->duration_minutes,
            ]),
            'device_id' => $this->device_id,
            'device' => $this->whenLoaded('device', fn () => $this->device ? [
                'id' => $this->device->id,
                'name' => $this->device->name,
            ] : null),
            'master_id' => $this->master_id,
            'master' => $this->whenLoaded('master', fn () => $this->master ? [
                'id' => $this->master->id,
                'name' => $this->master->name,
            ] : null),
            'client_package_id' => $this->client_package_id,
            'client_package' => $this->whenLoaded('clientPackage', fn () => $this->clientPackage ? [
                'id' => $this->clientPackage->id,
                'name' => $this->clientPackage->name,
                'remaining_procedures' => $this->clientPackage->remaining_procedures,
            ] : null),
            'zone' => $this->zone,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'price' => (float) $this->price,
            'payment_method' => $this->payment_method->value,
            'visit_status' => $this->visit_status->value,
            'master_comment' => $this->master_comment,
            'deduct_from_package' => $this->deduct_from_package,
            'package_redemption' => $this->whenLoaded('packageRedemption', fn () => $this->packageRedemption ? [
                'id' => $this->packageRedemption->id,
                'procedures_deducted' => $this->packageRedemption->procedures_deducted,
                'redeemed_at' => $this->packageRedemption->redeemed_at?->toIso8601String(),
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
