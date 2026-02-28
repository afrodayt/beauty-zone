<?php

namespace App\Services\Admin;

use App\Models\Visit;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class CalendarService
{
    /**
     * @return array<string, mixed>
     */
    public function events(string $view, string $date, ?int $masterId = null): array
    {
        $point = CarbonImmutable::parse($date)->utc();

        if ($view === 'week') {
            $fromUtc = $point->startOfWeek();
            $toUtc = $point->endOfWeek();
        } else {
            $fromUtc = $point->startOfDay();
            $toUtc = $point->endOfDay();
        }

        $events = Visit::query()
            ->with(['client:id,full_name', 'service:id,name,color,duration_minutes', 'master:id,name'])
            ->when($masterId !== null, static fn ($query) => $query->where('master_id', $masterId))
            ->whereBetween('starts_at', [$fromUtc, $toUtc])
            ->orderBy('starts_at')
            ->get();

        return [
            'view' => $view,
            'from_utc' => $fromUtc->toIso8601String(),
            'to_utc' => $toUtc->toIso8601String(),
            'events' => $this->mapEvents($events),
        ];
    }

    /**
     * @param  Collection<int, Visit>  $events
     * @return Collection<int, array<string, mixed>>
     */
    private function mapEvents(Collection $events): Collection
    {
        return $events->map(static function (Visit $visit): array {
            $durationMinutes = $visit->service?->duration_minutes ?? 60;
            $startsAt = $visit->starts_at;
            $endsAt = $startsAt?->copy()->addMinutes($durationMinutes);

            return [
                'id' => $visit->id,
                'title' => sprintf('%s - %s', $visit->client?->full_name ?? 'Client', $visit->service?->name ?? 'Service'),
                'starts_at' => $startsAt?->toIso8601String(),
                'ends_at' => $endsAt?->toIso8601String(),
                'color' => $visit->service?->color ?? '#6c757d',
                'zone' => $visit->zone,
                'visit_status' => $visit->visit_status->value,
                'master_name' => $visit->master?->name,
            ];
        });
    }
}
