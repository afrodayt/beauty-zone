<?php

namespace App\Http\Controllers\Api\Admin;

use App\DTO\Admin\VisitData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VisitStoreRequest;
use App\Http\Resources\Admin\VisitResource;
use App\Services\Admin\CalendarService;
use App\Services\Admin\VisitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function __construct(
        private readonly CalendarService $calendarService,
        private readonly VisitService $visitService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $view = in_array((string) $request->string('view', 'day'), ['day', 'week'], true)
            ? (string) $request->string('view', 'day')
            : 'day';

        $date = (string) $request->string('date', now('UTC')->toDateString());
        $masterId = $request->filled('master_id') ? (int) $request->integer('master_id') : null;

        return response()->json($this->calendarService->events($view, $date, $masterId));
    }

    public function quickStore(VisitStoreRequest $request): VisitResource
    {
        $visit = $this->visitService->create(VisitData::fromArray($request->validated()));

        return new VisitResource($visit);
    }
}
