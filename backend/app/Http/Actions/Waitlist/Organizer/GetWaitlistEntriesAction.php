<?php

namespace TitaKita\Http\Actions\Waitlist\Organizer;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\WaitlistEntryDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\Waitlist\WaitlistEntryResource;
use TitaKita\Services\Application\Handlers\Waitlist\GetWaitlistEntriesHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetWaitlistEntriesAction extends BaseAction
{
    public function __construct(
        private readonly GetWaitlistEntriesHandler $handler,
    )
    {
    }

    public function __invoke(Request $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $entries = $this->handler->handle(
            $eventId,
            $this->getPaginationQueryParams($request),
        );

        return $this->filterableResourceResponse(
            resource: WaitlistEntryResource::class,
            data: $entries,
            domainObject: WaitlistEntryDomainObject::class,
        );
    }
}
