<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ExpenseResource;
use App\Http\Resources\Admin\PaymentResource;
use App\Models\Expense;
use App\Models\Payment;
use App\Services\Admin\FinanceService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function __construct(private readonly FinanceService $financeService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        [$fromUtc, $toUtc] = $this->period($request);

        $summary = $this->financeService->summary($fromUtc, $toUtc);
        $payments = Payment::query()
            ->with('client:id,full_name,phone')
            ->whereBetween('paid_at', [$fromUtc, $toUtc])
            ->latest('paid_at')
            ->limit(50)
            ->get();
        $expenses = Expense::query()
            ->whereBetween('date', [$fromUtc->toDateString(), $toUtc->toDateString()])
            ->latest('date')
            ->limit(50)
            ->get();

        return response()->json([
            'period' => [
                'from' => $fromUtc->toIso8601String(),
                'to' => $toUtc->toIso8601String(),
            ],
            'summary' => $summary,
            'payments' => PaymentResource::collection($payments),
            'expenses' => ExpenseResource::collection($expenses),
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
