<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\FinanceService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function __construct(private readonly FinanceService $financeService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        [$fromUtc, $toUtc] = $this->period($request);

        $masterId = $request->filled('master_id') ? (int) $request->integer('master_id') : null;
        $serviceId = $request->filled('service_id') ? (int) $request->integer('service_id') : null;
        $deviceId = $request->filled('device_id') ? (int) $request->integer('device_id') : null;

        return response()->json([
            'period' => [
                'from' => $fromUtc->toIso8601String(),
                'to' => $toUtc->toIso8601String(),
            ],
            'summary' => $this->financeService->summary($fromUtc, $toUtc, $masterId, $serviceId, $deviceId),
            'reports' => $this->financeService->reports($fromUtc, $toUtc, $masterId, $serviceId, $deviceId),
        ]);
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function period(Request $request): array
    {
        $fromUtc = $request->filled('from')
            ? CarbonImmutable::parse((string) $request->string('from'))->utc()->startOfDay()
            : now('UTC')->startOfMonth()->toImmutable();
        $toUtc = $request->filled('to')
            ? CarbonImmutable::parse((string) $request->string('to'))->utc()->endOfDay()
            : now('UTC')->endOfDay()->toImmutable();

        return [$fromUtc, $toUtc];
    }
}
