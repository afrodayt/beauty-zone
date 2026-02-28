<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExpenseStoreRequest;
use App\Http\Requests\Admin\ExpenseUpdateRequest;
use App\Http\Resources\Admin\ExpenseResource;
use App\Models\Expense;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExpenseController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $from = $request->filled('from')
            ? CarbonImmutable::parse((string) $request->string('from'))->toDateString()
            : null;
        $to = $request->filled('to')
            ? CarbonImmutable::parse((string) $request->string('to'))->toDateString()
            : null;

        $expenses = Expense::query()
            ->when($request->filled('type'), static fn ($query): mixed => $query->where('type', (string) $request->string('type')))
            ->when($from !== null, static fn ($query): mixed => $query->where('date', '>=', $from))
            ->when($to !== null, static fn ($query): mixed => $query->where('date', '<=', $to))
            ->latest('date')
            ->paginate((int) $request->integer('per_page', 20));

        return ExpenseResource::collection($expenses);
    }

    public function store(ExpenseStoreRequest $request): ExpenseResource
    {
        $expense = Expense::query()->create([
            ...$request->validated(),
            'date' => CarbonImmutable::parse((string) $request->input('date'))->toDateString(),
        ]);

        return new ExpenseResource($expense);
    }

    public function show(Expense $expense): ExpenseResource
    {
        return new ExpenseResource($expense);
    }

    public function update(ExpenseUpdateRequest $request, Expense $expense): ExpenseResource
    {
        $expense->fill([
            ...$request->validated(),
            'date' => CarbonImmutable::parse((string) $request->input('date'))->toDateString(),
        ]);
        $expense->save();

        return new ExpenseResource($expense);
    }

    public function destroy(Expense $expense): JsonResponse
    {
        $expense->delete();

        return response()->json(['message' => 'Expense deleted']);
    }
}
