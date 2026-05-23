<?php

namespace TitaKita\Services\Application\Handlers\Admin;

use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Services\Application\Handlers\Admin\DTO\GetUpcomingEventsDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetUpcomingEventsHandler
{
    public function __construct(
        private readonly EventRepositoryInterface $eventRepository,
    )
    {
    }

    public function handle(GetUpcomingEventsDTO $dto): LengthAwarePaginator
    {
        return $this->eventRepository->getUpcomingEventsForAdmin($dto->perPage);
    }
}
