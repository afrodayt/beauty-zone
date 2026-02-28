<?php

namespace App\Http\Controllers\Api\Admin;

use App\DTO\Admin\ClientPackageData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ClientPackageStoreRequest;
use App\Http\Requests\Admin\ClientPackageUpdateRequest;
use App\Http\Resources\Admin\ClientPackageResource;
use App\Models\ClientPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class ClientPackageController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $packages = ClientPackage::query()
            ->with(['client:id,full_name,phone', 'template:id,name'])
            ->when(
                $request->filled('client_id'),
                static fn ($query): mixed => $query->where('client_id', (int) $request->integer('client_id'))
            )
            ->when($request->filled('status'), static fn ($query): mixed => $query->where('status', $request->string('status')))
            ->latest('created_at')
            ->paginate((int) $request->integer('per_page', 20));

        return ClientPackageResource::collection($packages);
    }

    public function store(ClientPackageStoreRequest $request): ClientPackageResource
    {
        $data = ClientPackageData::fromArray($request->validated());
        $this->validateProcedures($data->totalProcedures, $data->remainingProcedures);

        $package = ClientPackage::query()->create($this->mapData($data));

        return new ClientPackageResource($package->load(['client:id,full_name,phone', 'template:id,name']));
    }

    public function show(ClientPackage $clientPackage): ClientPackageResource
    {
        return new ClientPackageResource($clientPackage->load(['client:id,full_name,phone', 'template:id,name']));
    }

    public function update(
        ClientPackageUpdateRequest $request,
        ClientPackage $clientPackage
    ): ClientPackageResource {
        $data = ClientPackageData::fromArray($request->validated());
        $this->validateProcedures($data->totalProcedures, $data->remainingProcedures);

        $clientPackage->fill($this->mapData($data));
        $clientPackage->save();

        return new ClientPackageResource($clientPackage->load(['client:id,full_name,phone', 'template:id,name']));
    }

    public function destroy(ClientPackage $clientPackage): JsonResponse
    {
        $clientPackage->delete();

        return response()->json(['message' => 'Client package deleted']);
    }

    private function validateProcedures(int $total, int $remaining): void
    {
        if ($remaining > $total) {
            throw ValidationException::withMessages([
                'remaining_procedures' => ['Оставшиеся процедуры не могут быть больше общего количества.'],
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function mapData(ClientPackageData $data): array
    {
        return [
            'client_id' => $data->clientId,
            'package_template_id' => $data->packageTemplateId,
            'name' => $data->name,
            'total_procedures' => $data->totalProcedures,
            'remaining_procedures' => $data->remainingProcedures,
            'purchased_amount' => $data->purchasedAmount,
            'expires_at' => $data->expiresAtUtc,
            'status' => $data->status,
        ];
    }
}
