<?php

namespace TitaKita\Http\Actions\CheckInLists;

use TitaKita\DomainObjects\CheckInListDomainObject;
use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\CheckInList\CheckInListResource;
use TitaKita\Services\Application\Handlers\CheckInList\DTO\GetCheckInListsDTO;
use TitaKita\Services\Application\Handlers\CheckInList\GetCheckInListsHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetCheckInListsAction extends BaseAction
{
    public function __construct(
        private readonly GetCheckInListsHandler $getCheckInListsHandler,
    )
    {
    }

    public function __invoke(int $eventId, Request $request): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        return $this->filterableResourceResponse(
            resource: CheckInListResource::class,
            data: $this->getCheckInListsHandler->handle(
                GetCheckInListsDTO::fromArray([
                    'eventId' => $eventId,
                    'queryParams' => $this->getPaginationQueryParams($request),
                ]),
            ),
            domainObject: CheckInListDomainObject::class,
        );
    }
}
