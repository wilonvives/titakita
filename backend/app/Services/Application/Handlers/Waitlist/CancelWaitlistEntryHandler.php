<?php

namespace TitaKita\Services\Application\Handlers\Waitlist;

use TitaKita\DomainObjects\WaitlistEntryDomainObject;
use TitaKita\Exceptions\ResourceConflictException;
use TitaKita\Exceptions\ResourceNotFoundException;
use TitaKita\Services\Domain\Waitlist\CancelWaitlistEntryService;

class CancelWaitlistEntryHandler
{
    public function __construct(
        private readonly CancelWaitlistEntryService $cancelWaitlistEntryService,
    )
    {
    }

    /**
     * @throws ResourceConflictException
     * @throws ResourceNotFoundException
     */
    public function handleCancelByToken(string $cancelToken): WaitlistEntryDomainObject
    {
        return $this->cancelWaitlistEntryService->cancelByToken($cancelToken);
    }

    /**
     * @throws ResourceConflictException
     * @throws ResourceNotFoundException
     */
    public function handleCancelById(int $entryId, int $eventId): WaitlistEntryDomainObject
    {
        return $this->cancelWaitlistEntryService->cancelById($entryId, $eventId);
    }
}
