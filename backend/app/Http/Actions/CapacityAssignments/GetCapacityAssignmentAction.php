<?php

namespace TitaKita\Http\Actions\CapacityAssignments;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\CapacityAssignment\CapacityAssignmentResource;
use TitaKita\Services\Application\Handlers\CapacityAssignment\GetCapacityAssignmentHandler;
use Illuminate\Http\JsonResponse;

class GetCapacityAssignmentAction extends BaseAction
{
    public function __construct(
        private readonly GetCapacityAssignmentHandler $getCapacityAssignmentsHandler,
    )
    {
    }

    public function __invoke(int $eventId, int $capacityAssignmentId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        return $this->resourceResponse(
            resource: CapacityAssignmentResource::class,
            data: $this->getCapacityAssignmentsHandler->handle(
                capacityAssignmentId: $capacityAssignmentId,
                eventId: $eventId,
            ),
        );
    }
}
