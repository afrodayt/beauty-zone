<?php

namespace App\Services\Admin;

use App\DTO\Admin\VisitData;
use App\Enums\PackageStatus;
use App\Enums\VisitStatus;
use App\Models\Client;
use App\Models\ClientPackage;
use App\Models\PackageRedemption;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VisitService
{
    public function create(VisitData $data): Visit
    {
        return DB::transaction(function () use ($data): Visit {
            $this->ensureNoConflict($data);

            $visit = Visit::query()->create([
                'client_id' => $data->clientId,
                'service_id' => $data->serviceId,
                'device_id' => $data->deviceId,
                'master_id' => $data->masterId,
                'client_package_id' => $data->clientPackageId,
                'zone' => $data->zone,
                'starts_at' => $data->startsAtUtc,
                'price' => $data->price,
                'payment_method' => $data->paymentMethod,
                'visit_status' => $data->visitStatus,
                'master_comment' => $data->masterComment,
                'deduct_from_package' => $data->deductFromPackage,
            ]);

            $this->syncPackageRedemption($visit, $data);
            $this->syncClientLastVisit($visit);

            return $visit->fresh([
                'client',
                'service',
                'device',
                'master',
                'clientPackage',
                'packageRedemption',
            ]);
        });
    }

    public function update(Visit $visit, VisitData $data): Visit
    {
        return DB::transaction(function () use ($visit, $data): Visit {
            $this->ensureNoConflict($data, $visit->id);

            $visit->fill([
                'client_id' => $data->clientId,
                'service_id' => $data->serviceId,
                'device_id' => $data->deviceId,
                'master_id' => $data->masterId,
                'client_package_id' => $data->clientPackageId,
                'zone' => $data->zone,
                'starts_at' => $data->startsAtUtc,
                'price' => $data->price,
                'payment_method' => $data->paymentMethod,
                'visit_status' => $data->visitStatus,
                'master_comment' => $data->masterComment,
                'deduct_from_package' => $data->deductFromPackage,
            ]);
            $visit->save();

            $this->syncPackageRedemption($visit, $data);
            $this->syncClientLastVisit($visit);

            return $visit->fresh([
                'client',
                'service',
                'device',
                'master',
                'clientPackage',
                'packageRedemption',
            ]);
        });
    }

    public function delete(Visit $visit): void
    {
        DB::transaction(function () use ($visit): void {
            $this->revertRedemptionIfExists($visit);
            $visit->delete();
            $this->refreshClientLastVisit($visit->client_id);
        });
    }

    private function ensureNoConflict(VisitData $data, ?int $ignoreVisitId = null): void
    {
        if ($data->masterId === null) {
            return;
        }

        $query = Visit::query()
            ->where('master_id', $data->masterId)
            ->where('starts_at', $data->startsAtUtc)
            ->whereNotIn('visit_status', [VisitStatus::CANCELED->value, VisitStatus::NO_SHOW->value]);

        if ($ignoreVisitId !== null) {
            $query->where('id', '!=', $ignoreVisitId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'starts_at' => ['Этот мастер уже занят на указанное время.'],
            ]);
        }
    }

    private function syncPackageRedemption(Visit $visit, VisitData $data): void
    {
        $this->revertRedemptionIfExists($visit);

        if (! $data->deductFromPackage) {
            return;
        }

        if ($data->clientPackageId === null) {
            throw ValidationException::withMessages([
                'client_package_id' => ['Для списания процедуры выберите пакет.'],
            ]);
        }

        $package = ClientPackage::query()
            ->whereKey($data->clientPackageId)
            ->lockForUpdate()
            ->firstOrFail();

        if ($package->client_id !== $data->clientId) {
            throw ValidationException::withMessages([
                'client_package_id' => ['Пакет не принадлежит выбранному клиенту.'],
            ]);
        }

        if ($package->status !== PackageStatus::ACTIVE) {
            throw ValidationException::withMessages([
                'client_package_id' => ['Пакет неактивен и не может быть списан.'],
            ]);
        }

        if ($package->expires_at !== null && $package->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'client_package_id' => ['Срок действия пакета истек.'],
            ]);
        }

        if ($package->remaining_procedures <= 0) {
            throw ValidationException::withMessages([
                'client_package_id' => ['В пакете закончились процедуры.'],
            ]);
        }

        $package->remaining_procedures -= 1;
        if ($package->remaining_procedures === 0) {
            $package->status = PackageStatus::EXHAUSTED;
        }
        $package->save();

        PackageRedemption::query()->create([
            'client_package_id' => $package->id,
            'visit_id' => $visit->id,
            'procedures_deducted' => 1,
            'redeemed_at' => $data->startsAtUtc,
            'note' => 'Auto redemption on visit save',
        ]);
    }

    private function revertRedemptionIfExists(Visit $visit): void
    {
        $redemption = PackageRedemption::query()
            ->where('visit_id', $visit->id)
            ->lockForUpdate()
            ->first();

        if ($redemption === null) {
            return;
        }

        $package = ClientPackage::query()->whereKey($redemption->client_package_id)->lockForUpdate()->first();
        if ($package !== null) {
            $package->remaining_procedures = min(
                $package->total_procedures,
                $package->remaining_procedures + $redemption->procedures_deducted
            );

            if ($package->status === PackageStatus::EXHAUSTED && $package->remaining_procedures > 0) {
                $package->status = PackageStatus::ACTIVE;
            }

            $package->save();
        }

        $redemption->delete();
    }

    private function syncClientLastVisit(Visit $visit): void
    {
        if ($visit->visit_status === VisitStatus::ARRIVED) {
            Client::query()->whereKey($visit->client_id)->update(['last_visit_at' => $visit->starts_at]);
            return;
        }

        $this->refreshClientLastVisit($visit->client_id);
    }

    private function refreshClientLastVisit(int $clientId): void
    {
        $lastVisitAt = Visit::query()
            ->where('client_id', $clientId)
            ->where('visit_status', VisitStatus::ARRIVED->value)
            ->max('starts_at');

        Client::query()->whereKey($clientId)->update(['last_visit_at' => $lastVisitAt]);
    }
}
