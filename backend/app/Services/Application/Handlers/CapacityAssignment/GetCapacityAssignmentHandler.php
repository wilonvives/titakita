<?php

namespace TitaKita\Services\Application\Handlers\CapacityAssignment;

use TitaKita\DomainObjects\CapacityAssignmentDomainObject;
use TitaKita\DomainObjects\ProductDomainObject;
use TitaKita\Repository\Interfaces\CapacityAssignmentRepositoryInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class GetCapacityAssignmentHandler
{
    public function __construct(
        private readonly CapacityAssignmentRepositoryInterface $capacityAssignmentRepository,
    )
    {
    }

    public function handle(int $capacityAssignmentId, int $eventId): CapacityAssignmentDomainObject
    {
        $capacityAssignment = $this->capacityAssignmentRepository
            ->loadRelation(ProductDomainObject::class)
            ->findFirstWhere([
                'event_id' => $eventId,
                'id' => $capacityAssignmentId,
            ]);

        if ($capacityAssignment === null) {
            throw new ResourceNotFoundException('Capacity assignment not found');
        }

        return $capacityAssignment;
    }
}
