<?php

namespace App\Services\Admin;

use App\Enums\VisitStatus;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Visit;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class FinanceService
{
    /**
     * @return array<string, float|int>
     */
    public function summary(
        CarbonImmutable $fromUtc,
        CarbonImmutable $toUtc,
        ?int $masterId = null,
        ?int $serviceId = null,
        ?int $deviceId = null
    ): array {
        $paymentsQuery = Payment::query()
            ->whereBetween('paid_at', [$fromUtc, $toUtc]);

        $revenue = (float) $paymentsQuery->sum('amount');
        $checksCount = (int) $paymentsQuery->count('id');
        $expenses = (float) Expense::query()
            ->whereBetween('date', [$fromUtc->toDateString(), $toUtc->toDateString()])
            ->sum('amount');

        $deviceRevenue = $this->applyVisitFilters(
            Visit::query()
                ->where('visit_status', VisitStatus::ARRIVED->value)
                ->whereNotNull('device_id')
                ->whereBetween('starts_at', [$fromUtc, $toUtc]),
            $masterId,
            $serviceId,
            $deviceId
        )->sum('price');

        return [
            'revenue' => round($revenue, 2),
            'expenses' => round($expenses, 2),
            'profit' => round($revenue - $expenses, 2),
            'average_check' => round($checksCount > 0 ? $revenue / $checksCount : 0, 2),
            'device_revenue' => round((float) $deviceRevenue, 2),
            'checks_count' => $checksCount,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function reports(
        CarbonImmutable $fromUtc,
        CarbonImmutable $toUtc,
        ?int $masterId = null,
        ?int $serviceId = null,
        ?int $deviceId = null
    ): array {
        $services = $this->applyVisitFilters(
            Visit::query()
                ->select([
                    'service_id',
                    DB::raw('COUNT(*) as visits_count'),
                    DB::raw('SUM(price) as revenue'),
                ])
                ->where('visit_status', VisitStatus::ARRIVED->value)
                ->whereBetween('starts_at', [$fromUtc, $toUtc])
                ->groupBy('service_id')
                ->with('service:id,name,color'),
            $masterId,
            $serviceId,
            $deviceId
        )->get();

        $masters = $this->applyVisitFilters(
            Visit::query()
                ->select([
                    'master_id',
                    DB::raw('COUNT(*) as visits_count'),
                    DB::raw('SUM(price) as revenue'),
                ])
                ->where('visit_status', VisitStatus::ARRIVED->value)
                ->whereBetween('starts_at', [$fromUtc, $toUtc])
                ->groupBy('master_id')
                ->with('master:id,name,email'),
            $masterId,
            $serviceId,
            $deviceId
        )->get();

        $clients = $this->applyVisitFilters(
            Visit::query()
                ->select([
                    'client_id',
                    DB::raw('COUNT(*) as visits_count'),
                    DB::raw('SUM(price) as charges'),
                ])
                ->where('visit_status', VisitStatus::ARRIVED->value)
                ->whereBetween('starts_at', [$fromUtc, $toUtc])
                ->groupBy('client_id')
                ->with('client:id,full_name,phone'),
            $masterId,
            $serviceId,
            $deviceId
        )->get();

        $daily = Payment::query()
            ->select([
                DB::raw('DATE(paid_at) as date'),
                DB::raw('SUM(amount) as revenue'),
            ])
            ->whereBetween('paid_at', [$fromUtc, $toUtc])
            ->groupBy(DB::raw('DATE(paid_at)'))
            ->orderBy('date')
            ->get();

        return [
            'services' => $services->map(function (Visit $visit): array {
                return [
                    'service_id' => $visit->service_id,
                    'service_name' => $visit->service?->name,
                    'color' => $visit->service?->color,
                    'visits_count' => (int) $visit->visits_count,
                    'revenue' => round((float) $visit->revenue, 2),
                ];
            }),
            'masters' => $masters->map(function (Visit $visit): array {
                return [
                    'master_id' => $visit->master_id,
                    'master_name' => $visit->master?->name,
                    'visits_count' => (int) $visit->visits_count,
                    'revenue' => round((float) $visit->revenue, 2),
                ];
            }),
            'clients' => $clients->map(function (Visit $visit): array {
                return [
                    'client_id' => $visit->client_id,
                    'client_name' => $visit->client?->full_name,
                    'phone' => $visit->client?->phone,
                    'visits_count' => (int) $visit->visits_count,
                    'charges' => round((float) $visit->charges, 2),
                    'payments' => $this->clientPaymentsInPeriod((int) $visit->client_id, $fromUtc, $toUtc),
                ];
            }),
            'daily_finance' => $daily->map(function (Payment $payment): array {
                return [
                    'date' => (string) $payment->date,
                    'revenue' => round((float) $payment->revenue, 2),
                ];
            }),
        ];
    }

    public function clientBalance(int $clientId): float
    {
        $payments = (float) Payment::query()->where('client_id', $clientId)->sum('amount');
        $charges = (float) Visit::query()
            ->where('client_id', $clientId)
            ->where('visit_status', VisitStatus::ARRIVED->value)
            ->sum('price');

        return round($payments - $charges, 2);
    }

    private function clientPaymentsInPeriod(int $clientId, CarbonImmutable $fromUtc, CarbonImmutable $toUtc): float
    {
        return round((float) Payment::query()
            ->where('client_id', $clientId)
            ->whereBetween('paid_at', [$fromUtc, $toUtc])
            ->sum('amount'), 2);
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    private function applyVisitFilters(
        Builder $query,
        ?int $masterId = null,
        ?int $serviceId = null,
        ?int $deviceId = null
    ): Builder {
        return $query
            ->when($masterId !== null, static fn (Builder $builder): Builder => $builder->where('master_id', $masterId))
            ->when($serviceId !== null, static fn (Builder $builder): Builder => $builder->where('service_id', $serviceId))
            ->when($deviceId !== null, static fn (Builder $builder): Builder => $builder->where('device_id', $deviceId));
    }
}
