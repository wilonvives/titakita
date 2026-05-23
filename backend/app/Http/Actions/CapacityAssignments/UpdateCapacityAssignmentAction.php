<?php

namespace TitaKita\Http\Actions\CapacityAssignments;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\CapacityAssigment\UpsertCapacityAssignmentRequest;
use TitaKita\Resources\CapacityAssignment\CapacityAssignmentResource;
use TitaKita\Services\Application\Handlers\CapacityAssignment\DTO\UpsertCapacityAssignmentDTO;
use TitaKita\Services\Application\Handlers\CapacityAssignment\UpdateCapacityAssignmentHandler;
use TitaKita\Services\Domain\Product\Exception\UnrecognizedProductIdException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UpdateCapacityAssignmentAction extends BaseAction
{
    public function __construct(
        private readonly UpdateCapacityAssignmentHandler $updateCapacityAssignmentHandler,
    )
    {
    }

    public function __invoke(int $eventId, int $capacityAssignmentId, UpsertCapacityAssignmentRequest $request): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        try {
            $assignment = $this->updateCapacityAssignmentHandler->handle(
                UpsertCapacityAssignmentDTO::fromArray([
                    'id' => $capacityAssignmentId,
                    'name' => $request->validated('name'),
                    'event_id' => $eventId,
                    'capacity' => $request->validated('capacity'),
                    'applies_to' => $request->validated('applies_to'),
                    'status' => $request->validated('status'),
                    'product_ids' => $request->validated('product_ids'),
                ]),
            );
        } catch (UnrecognizedProductIdException $exception) {
            return $this->errorResponse(
                message: $exception->getMessage(),
                statusCode: Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        return $this->resourceResponse(
            resource: CapacityAssignmentResource::class,
            data: $assignment,
        );
    }
}
