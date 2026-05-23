<?php

namespace TitaKita\Http\Actions\Events\Stats;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Services\Application\Handlers\Event\DTO\EventStatsRequestDTO;
use TitaKita\Services\Application\Handlers\Event\GetEventStatsHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GetEventStatsAction extends BaseAction
{
    public function __construct(
        private readonly GetEventStatsHandler $eventStatsHandler
    )
    {
    }

    public function __invoke(int $eventId, Request $request): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $dateRangePreset = $request->query('date_range', 'month');

        $stats = $this->eventStatsHandler->handle(EventStatsRequestDTO::fromArray([
            'event_id' => $eventId,
            'date_range_preset' => $dateRangePreset,
        ]));

        return $this->resourceResponse(JsonResource::class, $stats);
    }
}
