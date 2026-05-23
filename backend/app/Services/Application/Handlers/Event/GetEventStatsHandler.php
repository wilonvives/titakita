<?php

namespace TitaKita\Services\Application\Handlers\Event;

use TitaKita\Services\Application\Handlers\Event\DTO\EventStatsRequestDTO;
use TitaKita\Services\Application\Handlers\Event\DTO\EventStatsResponseDTO;
use TitaKita\Services\Domain\Event\EventStatsFetchService;

readonly class GetEventStatsHandler
{
    public function __construct(private EventStatsFetchService $eventStatsFetchService)
    {
    }

    public function handle(EventStatsRequestDTO $statsRequestDTO): EventStatsResponseDTO
    {
        return $this->eventStatsFetchService->getEventStats($statsRequestDTO);
    }
}
