<?php

namespace App\Http\Controllers\Api\Admin;

use App\DTO\Admin\ClientData;
use App\Enums\ClientStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ClientStoreRequest;
use App\Http\Requests\Admin\ClientUpdateRequest;
use App\Http\Resources\Admin\ClientPackageResource;
use App\Http\Resources\Admin\ClientResource;
use App\Http\Resources\Admin\PaymentResource;
use App\Http\Resources\Admin\VisitResource;
use App\Models\Client;
use App\Services\Admin\FinanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientController extends Controller
{
    public function __construct(private readonly FinanceService $financeService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $search = trim((string) $request->string('search', ''));
        $status = $request->string('status')->toString();

        $clients = Client::query()
            ->when($search !== '', static function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', static fn ($query): mixed => $query->where('status', $status))
            ->orderByDesc('updated_at')
            ->paginate((int) $request->integer('per_page', 20));

        $clients->getCollection()->transform(function (Client $client): Client {
            $client->setAttribute('balance_eur', $this->financeService->clientBalance($client->id));

            return $client;
        });

        return ClientResource::collection($clients);
    }

    public function store(ClientStoreRequest $request): ClientResource
    {
        $data = ClientData::fromArray([
            ...$request->validated(),
            'status' => $request->input('status', ClientStatus::NEW->value),
        ]);

        $client = Client::query()->create($this->mapClientData($data));
        $client->setAttribute('balance_eur', 0);

        return new ClientResource($client);
    }

    public function show(Client $client): JsonResponse
    {
        $client->setAttribute('balance_eur', $this->financeService->clientBalance($client->id));

        $visits = $client->visits()
            ->with(['service', 'device', 'master', 'clientPackage', 'packageRedemption'])
            ->latest('starts_at')
            ->limit(30)
            ->get();

        $payments = $client->payments()
            ->with('visit:id,starts_at')
            ->latest('paid_at')
            ->limit(30)
            ->get();

        $packages = $client->clientPackages()
            ->with('template:id,name')
            ->latest('created_at')
            ->get();

        return response()->json([
            'client' => new ClientResource($client),
            'visits' => VisitResource::collection($visits),
            'payments' => PaymentResource::collection($payments),
            'packages' => ClientPackageResource::collection($packages),
        ]);
    }

    public function update(ClientUpdateRequest $request, Client $client): ClientResource
    {
        $data = ClientData::fromArray([
            ...$request->validated(),
            'status' => $request->input('status', $client->status->value),
        ]);

        $client->fill($this->mapClientData($data));
        $client->save();
        $client->setAttribute('balance_eur', $this->financeService->clientBalance($client->id));

        return new ClientResource($client);
    }

    public function destroy(Client $client): JsonResponse
    {
        $client->delete();

        return response()->json(['message' => 'Client deleted']);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapClientData(ClientData $data): array
    {
        return [
            'full_name' => $data->fullName,
            'phone' => $data->phone,
            'birth_date' => $data->birthDate,
            'status' => $data->status,
            'notes' => $data->notes,
            'contra_pregnancy' => $data->contraPregnancy,
            'contra_allergy' => $data->contraAllergy,
            'contra_skin_damage' => $data->contraSkinDamage,
            'contra_varicose' => $data->contraVaricose,
        ];
    }
}
