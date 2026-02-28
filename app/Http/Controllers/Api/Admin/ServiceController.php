<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceStoreRequest;
use App\Http\Requests\Admin\ServiceUpdateRequest;
use App\Http\Resources\Admin\ServiceResource;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $services = Service::query()
            ->when($request->has('active'), static fn ($query): mixed => $query->where('is_active', $request->boolean('active')))
            ->orderBy('name')
            ->paginate((int) $request->integer('per_page', 50));

        return ServiceResource::collection($services);
    }

    public function store(ServiceStoreRequest $request): ServiceResource
    {
        $service = Service::query()->create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return new ServiceResource($service);
    }

    public function show(Service $service): ServiceResource
    {
        return new ServiceResource($service);
    }

    public function update(ServiceUpdateRequest $request, Service $service): ServiceResource
    {
        $service->fill([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', $service->is_active),
        ]);
        $service->save();

        return new ServiceResource($service);
    }

    public function destroy(Service $service): JsonResponse
    {
        $service->delete();

        return response()->json(['message' => 'Service deleted']);
    }
}
