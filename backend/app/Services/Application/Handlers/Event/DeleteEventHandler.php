<?php

namespace TitaKita\Services\Application\Handlers\Event;

use TitaKita\Exceptions\CannotDeleteEntityException;
use TitaKita\Services\Application\Handlers\Event\DTO\DeleteEventDTO;
use TitaKita\Services\Domain\Event\EventDeletionService;
use Throwable;

class DeleteEventHandler
{
    public function __construct(
        private readonly EventDeletionService $eventDeletionService,
    )
    {
    }

    /**
     * @throws CannotDeleteEntityException
     * @throws Throwable
     */
    public function handle(DeleteEventDTO $dto): void
    {
        $this->eventDeletionService->deleteEvent($dto->eventId, $dto->accountId);
    }
}
