<?php

namespace TitaKita\Http\Actions\Attendees;

use TitaKita\DomainObjects\AttendeeDomainObject;
use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\DTO\QueryParamsDTO;
use TitaKita\Resources\Attendee\AttendeeResource;
use TitaKita\Services\Application\Handlers\Attendee\GetAttendeesHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetAttendeesAction extends BaseAction
{
    public function __construct(
        private readonly GetAttendeesHandler $getAttendeesHandler,
    )
    {
    }

    public function __invoke(int $eventId, Request $request): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $attendees = $this->getAttendeesHandler->handle(
            eventId: $eventId,
            queryParams: QueryParamsDTO::fromArray($request->query->all())
        );

        return $this->filterableResourceResponse(
            resource: AttendeeResource::class,
            data: $attendees,
            domainObject: AttendeeDomainObject::class,
        );
    }
}
