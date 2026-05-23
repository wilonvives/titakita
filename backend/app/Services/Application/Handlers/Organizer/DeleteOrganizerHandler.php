<?php

namespace TitaKita\Services\Application\Handlers\Organizer;

use TitaKita\Exceptions\CannotDeleteEntityException;
use TitaKita\Services\Application\Handlers\Organizer\DTO\DeleteOrganizerDTO;
use TitaKita\Services\Domain\Organizer\OrganizerDeletionService;
use Throwable;

class DeleteOrganizerHandler
{
    public function __construct(
        private readonly OrganizerDeletionService $organizerDeletionService,
    )
    {
    }

    /**
     * @throws CannotDeleteEntityException
     * @throws Throwable
     */
    public function handle(DeleteOrganizerDTO $dto): void
    {
        $this->organizerDeletionService->deleteOrganizer($dto->organizerId, $dto->accountId);
    }
}
