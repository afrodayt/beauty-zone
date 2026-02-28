<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserStoreRequest;
use App\Http\Resources\Admin\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = trim((string) $request->string('search', ''));
        $role = $request->string('role')->toString();

        $users = User::query()
            ->when($search !== '', static function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($role !== '', static fn ($query): mixed => $query->where('role', $role))
            ->orderBy('name')
            ->paginate((int) $request->integer('per_page', 20));

        return UserResource::collection($users);
    }

    public function store(UserStoreRequest $request): UserResource
    {
        $user = User::query()->create($request->validated());

        return new UserResource($user);
    }
}
