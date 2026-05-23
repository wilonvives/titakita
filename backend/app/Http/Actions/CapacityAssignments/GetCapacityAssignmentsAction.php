<?php

namespace TitaKita\Http\Actions\CapacityAssignments;

use TitaKita\DomainObjects\CapacityAssignmentDomainObject;
use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\CapacityAssignment\CapacityAssignmentResource;
use TitaKita\Services\Application\Handlers\CapacityAssignment\DTO\GetCapacityAssignmentsDTO;
use TitaKita\Services\Application\Handlers\CapacityAssignment\GetCapacityAssignmentsHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetCapacityAssignmentsAction extends BaseAction
{
    public function __construct(
        private readonly GetCapacityAssignmentsHandler $getCapacityAssignmentsHandler,
    )
    {
    }

    public function __invoke(int $eventId, Request $request): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        return $this->filterableResourceResponse(
            resource: CapacityAssignmentResource::class,
            data: $this->getCapacityAssignmentsHandler->handle(
                GetCapacityAssignmentsDTO::fromArray([
                    'eventId' => $eventId,
                    'queryParams' => $this->getPaginationQueryParams($request),
                ]),
            ),
            domainObject: CapacityAssignmentDomainObject::class,
        );
    }
}
