<?php

namespace TitaKita\Http\Actions\CheckInLists;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\CheckInList\CheckInListResource;
use TitaKita\Services\Application\Handlers\CheckInList\GetCheckInListHandler;
use Illuminate\Http\JsonResponse;

class GetCheckInListAction extends BaseAction
{
    public function __construct(
        private readonly GetCheckInListHandler $getCheckInListHandler,
    )
    {
    }

    public function __invoke(int $eventId, int $checkInListId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $checkInList = $this->getCheckInListHandler->handle(
            checkInListId: $checkInListId,
            eventId: $eventId,
        );

        return $this->resourceResponse(
            resource: CheckInListResource::class,
            data: $checkInList,
        );
    }
}
