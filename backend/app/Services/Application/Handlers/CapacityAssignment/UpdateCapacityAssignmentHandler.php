<?php

namespace TitaKita\Services\Application\Handlers\CapacityAssignment;

use TitaKita\DomainObjects\CapacityAssignmentDomainObject;
use TitaKita\DomainObjects\Enums\CapacityAssignmentAppliesTo;
use TitaKita\DomainObjects\Enums\CapacityChangeDirection;
use TitaKita\Events\CapacityChangedEvent;
use TitaKita\Repository\Interfaces\CapacityAssignmentRepositoryInterface;
use TitaKita\Services\Application\Handlers\CapacityAssignment\DTO\UpsertCapacityAssignmentDTO;
use TitaKita\Services\Domain\CapacityAssignment\UpdateCapacityAssignmentService;
use TitaKita\Services\Domain\Product\Exception\UnrecognizedProductIdException;

class UpdateCapacityAssignmentHandler
{
    public function __construct(
        private readonly UpdateCapacityAssignmentService        $updateCapacityAssignmentService,
        private readonly CapacityAssignmentRepositoryInterface  $capacityAssignmentRepository,
    )
    {
    }

    /**
     * @throws UnrecognizedProductIdException
     */
    public function handle(UpsertCapacityAssignmentDTO $data): CapacityAssignmentDomainObject
    {
        $existingAssignment = $this->capacityAssignmentRepository->findById($data->id);

        $capacityAssignment = (new CapacityAssignmentDomainObject)
            ->setId($data->id)
            ->setName($data->name)
            ->setEventId($data->event_id)
            ->setCapacity($data->capacity)
            ->setAppliesTo(CapacityAssignmentAppliesTo::PRODUCTS->name)
            ->setStatus($data->status->name);

        $result = $this->updateCapacityAssignmentService->updateCapacityAssignment(
            $capacityAssignment,
            $data->product_ids,
        );

        $this->dispatchCapacityChangedEvents(
            $existingAssignment,
            $data,
        );

        return $result;
    }

    private function dispatchCapacityChangedEvents(
        CapacityAssignmentDomainObject $existingAssignment,
        UpsertCapacityAssignmentDTO    $data,
    ): void
    {
        if (empty($data->product_ids)) {
            return;
        }

        $oldCapacity = $existingAssignment->getCapacity();
        $newCapacity = $data->capacity;

        $direction = match (true) {
            ($newCapacity === null && $oldCapacity !== null),
            ($newCapacity !== null && $oldCapacity !== null && $newCapacity > $oldCapacity)
                => CapacityChangeDirection::INCREASED,
            ($newCapacity !== null && $oldCapacity === null),
            ($newCapacity !== null && $oldCapacity !== null && $newCapacity < $oldCapacity)
                => CapacityChangeDirection::DECREASED,
            default => null,
        };

        if ($direction === null) {
            return;
        }

        foreach ($data->product_ids as $productId) {
            event(new CapacityChangedEvent(
                eventId: $data->event_id,
                direction: $direction,
                productId: $productId,
                newCapacity: $data->capacity,
            ));
        }
    }
}
