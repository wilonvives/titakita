<?php

namespace TitaKita\Services\Application\Handlers\CapacityAssignment;

use TitaKita\DomainObjects\CapacityAssignmentDomainObject;
use TitaKita\DomainObjects\Enums\CapacityAssignmentAppliesTo;
use TitaKita\Services\Application\Handlers\CapacityAssignment\DTO\UpsertCapacityAssignmentDTO;
use TitaKita\Services\Domain\CapacityAssignment\CreateCapacityAssignmentService;
use TitaKita\Services\Domain\Product\Exception\UnrecognizedProductIdException;

class CreateCapacityAssignmentHandler
{
    public function __construct(
        private readonly CreateCapacityAssignmentService $createCapacityAssignmentService
    )
    {
    }

    /**
     * @throws UnrecognizedProductIdException
     */
    public function handle(UpsertCapacityAssignmentDTO $data): CapacityAssignmentDomainObject
    {
        $capacityAssignment = (new CapacityAssignmentDomainObject)
            ->setName($data->name)
            ->setEventId($data->event_id)
            ->setCapacity($data->capacity)
            ->setAppliesTo(CapacityAssignmentAppliesTo::PRODUCTS->name)
            ->setStatus($data->status->name);

        return $this->createCapacityAssignmentService->createCapacityAssignment(
            $capacityAssignment,
            $data->product_ids,
        );
    }
}
