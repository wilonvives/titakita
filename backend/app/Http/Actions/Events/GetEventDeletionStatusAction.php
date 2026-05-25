<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Events;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Services\Domain\Event\EventDeletionService;
use Illuminate\Http\JsonResponse;

class GetEventDeletionStatusAction extends BaseAction
{
    public function __construct(
        private readonly EventDeletionService $eventDeletionService,
    )
    {
    }

    public function __invoke(int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $canDelete = $this->eventDeletionService->canDeleteEvent($eventId);

        return $this->jsonResponse([
            'data' => [
                'can_delete' => $canDelete,
                'reason' => $canDelete ? null : __('This event has paid attendees with payments that have not been refunded. Please refund each attendee from the Orders page before deleting.'),
            ],
        ]);
    }
}
