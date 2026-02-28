<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PackageTemplateStoreRequest;
use App\Http\Requests\Admin\PackageTemplateUpdateRequest;
use App\Http\Resources\Admin\PackageTemplateResource;
use App\Models\PackageTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PackageTemplateController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $templates = PackageTemplate::query()
            ->with('service:id,name,color')
            ->when($request->has('active'), static fn ($query): mixed => $query->where('is_active', $request->boolean('active')))
            ->orderByDesc('created_at')
            ->paginate((int) $request->integer('per_page', 20));

        return PackageTemplateResource::collection($templates);
    }

    public function store(PackageTemplateStoreRequest $request): PackageTemplateResource
    {
        $template = PackageTemplate::query()->create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return new PackageTemplateResource($template->load('service:id,name,color'));
    }

    public function show(PackageTemplate $packageTemplate): PackageTemplateResource
    {
        return new PackageTemplateResource($packageTemplate->load('service:id,name,color'));
    }

    public function update(
        PackageTemplateUpdateRequest $request,
        PackageTemplate $packageTemplate
    ): PackageTemplateResource {
        $packageTemplate->fill([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', $packageTemplate->is_active),
        ]);
        $packageTemplate->save();

        return new PackageTemplateResource($packageTemplate->load('service:id,name,color'));
    }

    public function destroy(PackageTemplate $packageTemplate): JsonResponse
    {
        $packageTemplate->delete();

        return response()->json(['message' => 'Package template deleted']);
    }
}
