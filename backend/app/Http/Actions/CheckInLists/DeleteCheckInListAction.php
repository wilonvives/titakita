<?php

namespace TitaKita\Http\Actions\CheckInLists;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Services\Application\Handlers\CheckInList\DeleteCheckInListHandler;
use Illuminate\Http\Response;

class DeleteCheckInListAction extends BaseAction
{
    public function __construct(
        private readonly DeleteCheckInListHandler $deleteCheckInListHandler,
    )
    {
    }

    public function __invoke(int $eventId, int $checkInListId): Response
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $this->deleteCheckInListHandler->handle(
            eventId: $eventId,
            checkInListId: $checkInListId,
        );

        return $this->noContentResponse();
    }
}
