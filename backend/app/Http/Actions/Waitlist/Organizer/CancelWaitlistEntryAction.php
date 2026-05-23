<?php

namespace TitaKita\Http\Actions\Waitlist\Organizer;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Exceptions\ResourceConflictException;
use TitaKita\Exceptions\ResourceNotFoundException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Services\Application\Handlers\Waitlist\CancelWaitlistEntryHandler;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CancelWaitlistEntryAction extends BaseAction
{
    public function __construct(
        private readonly CancelWaitlistEntryHandler $cancelWaitlistEntryHandler,
    )
    {
    }

    public function __invoke(int $eventId, int $entryId): Response|\Illuminate\Http\JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        try {
            $this->cancelWaitlistEntryHandler->handleCancelById($entryId, $eventId);
        } catch (ResourceNotFoundException $e) {
            return $this->errorResponse(
                message: $e->getMessage(),
                statusCode: SymfonyResponse::HTTP_NOT_FOUND,
            );
        } catch (ResourceConflictException $e) {
            return $this->errorResponse(
                message: $e->getMessage(),
                statusCode: SymfonyResponse::HTTP_CONFLICT,
            );
        }

        return $this->noContentResponse();
    }
}
