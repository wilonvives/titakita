<?php

namespace TitaKita\Http\Actions\Organizers;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\OrganizerDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\DTO\QueryParamsDTO;
use TitaKita\Resources\Event\EventResource;
use TitaKita\Services\Application\Handlers\Organizer\DTO\GetOrganizerEventsDTO;
use TitaKita\Services\Application\Handlers\Organizer\GetOrganizerEventsHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetOrganizerEventsAction extends BaseAction
{
    public function __construct(
        private readonly GetOrganizerEventsHandler $getOrganizerEventsHandler,
    )
    {
    }

    public function __invoke(int $organizerId, Request $request): JsonResponse
    {
        $this->isActionAuthorized(
            entityId: $organizerId,
            entityType: OrganizerDomainObject::class
        );

        $events = $this->getOrganizerEventsHandler->handle(new GetOrganizerEventsDTO(
            organizerId: $organizerId,
            accountId: $this->getAuthenticatedAccountId(),
            queryParams: QueryParamsDTO::fromArray($request->query->all())
        ));

        return $this->filterableResourceResponse(
            resource: EventResource::class,
            data: $events,
            domainObject: EventDomainObject::class
        );
    }
}
