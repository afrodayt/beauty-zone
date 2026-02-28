<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PaymentStoreRequest;
use App\Http\Requests\Admin\PaymentUpdateRequest;
use App\Http\Resources\Admin\PaymentResource;
use App\Models\Payment;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $fromUtc = $request->filled('from')
            ? CarbonImmutable::parse((string) $request->string('from'))->utc()
            : null;
        $toUtc = $request->filled('to')
            ? CarbonImmutable::parse((string) $request->string('to'))->utc()
            : null;

        $payments = Payment::query()
            ->with('client:id,full_name,phone')
            ->when($request->filled('client_id'), static fn ($query): mixed => $query->where('client_id', (int) $request->integer('client_id')))
            ->when($fromUtc !== null, static fn ($query): mixed => $query->where('paid_at', '>=', $fromUtc))
            ->when($toUtc !== null, static fn ($query): mixed => $query->where('paid_at', '<=', $toUtc))
            ->latest('paid_at')
            ->paginate((int) $request->integer('per_page', 20));

        return PaymentResource::collection($payments);
    }

    public function store(PaymentStoreRequest $request): PaymentResource
    {
        $payment = Payment::query()->create([
            ...$request->validated(),
            'paid_at' => CarbonImmutable::parse((string) $request->input('paid_at'))->utc(),
        ]);

        return new PaymentResource($payment->load('client:id,full_name,phone'));
    }

    public function show(Payment $payment): PaymentResource
    {
        return new PaymentResource($payment->load('client:id,full_name,phone'));
    }

    public function update(PaymentUpdateRequest $request, Payment $payment): PaymentResource
    {
        $payment->fill([
            ...$request->validated(),
            'paid_at' => CarbonImmutable::parse((string) $request->input('paid_at'))->utc(),
        ]);
        $payment->save();

        return new PaymentResource($payment->load('client:id,full_name,phone'));
    }

    public function destroy(Payment $payment): JsonResponse
    {
        $payment->delete();

        return response()->json(['message' => 'Payment deleted']);
    }
}
