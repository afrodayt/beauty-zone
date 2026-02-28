<?php

namespace App\Http\Controllers\Api\Admin;

use App\DTO\Admin\VisitData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VisitStoreRequest;
use App\Http\Requests\Admin\VisitUpdateRequest;
use App\Http\Resources\Admin\VisitResource;
use App\Models\Visit;
use App\Services\Admin\VisitService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VisitController extends Controller
{
    public function __construct(private readonly VisitService $visitService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $fromUtc = $request->filled('from')
            ? CarbonImmutable::parse((string) $request->string('from'))->utc()
            : null;
        $toUtc = $request->filled('to')
            ? CarbonImmutable::parse((string) $request->string('to'))->utc()
            : null;

        $visits = Visit::query()
            ->with(['client:id,full_name,phone', 'service:id,name,color,duration_minutes', 'device:id,name', 'master:id,name', 'clientPackage:id,name,remaining_procedures', 'packageRedemption'])
            ->when($request->filled('client_id'), static fn ($query): mixed => $query->where('client_id', (int) $request->integer('client_id')))
            ->when($request->filled('master_id'), static fn ($query): mixed => $query->where('master_id', (int) $request->integer('master_id')))
            ->when($request->filled('service_id'), static fn ($query): mixed => $query->where('service_id', (int) $request->integer('service_id')))
            ->when($request->filled('device_id'), static fn ($query): mixed => $query->where('device_id', (int) $request->integer('device_id')))
            ->when($request->filled('visit_status'), static fn ($query): mixed => $query->where('visit_status', (string) $request->string('visit_status')))
            ->when($fromUtc !== null, static fn ($query): mixed => $query->where('starts_at', '>=', $fromUtc))
            ->when($toUtc !== null, static fn ($query): mixed => $query->where('starts_at', '<=', $toUtc))
            ->orderByDesc('starts_at')
            ->paginate((int) $request->integer('per_page', 20));

        return VisitResource::collection($visits);
    }

    public function store(VisitStoreRequest $request): VisitResource
    {
        $visit = $this->visitService->create(VisitData::fromArray($request->validated()));

        return new VisitResource($visit);
    }

    public function show(Visit $visit): VisitResource
    {
        return new VisitResource($visit->load(['client:id,full_name,phone', 'service:id,name,color,duration_minutes', 'device:id,name', 'master:id,name', 'clientPackage:id,name,remaining_procedures', 'packageRedemption']));
    }

    public function update(VisitUpdateRequest $request, Visit $visit): VisitResource
    {
        $visit = $this->visitService->update($visit, VisitData::fromArray($request->validated()));

        return new VisitResource($visit);
    }

    public function destroy(Visit $visit): array
    {
        $this->visitService->delete($visit);

        return ['message' => 'Visit deleted'];
    }
}
