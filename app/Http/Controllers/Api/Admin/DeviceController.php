<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeviceStoreRequest;
use App\Http\Requests\Admin\DeviceUpdateRequest;
use App\Http\Resources\Admin\DeviceResource;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DeviceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $devices = Device::query()
            ->orderByDesc('created_at')
            ->paginate((int) $request->integer('per_page', 20));

        return DeviceResource::collection($devices);
    }

    public function store(DeviceStoreRequest $request): DeviceResource
    {
        $device = Device::query()->create($request->validated());

        return new DeviceResource($device);
    }

    public function show(Device $device): DeviceResource
    {
        return new DeviceResource($device);
    }

    public function update(DeviceUpdateRequest $request, Device $device): DeviceResource
    {
        $device->fill($request->validated());
        $device->save();

        return new DeviceResource($device);
    }

    public function destroy(Device $device): JsonResponse
    {
        $device->delete();

        return response()->json(['message' => 'Device deleted']);
    }
}
