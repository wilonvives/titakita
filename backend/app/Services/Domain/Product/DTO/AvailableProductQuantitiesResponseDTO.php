<?php

namespace TitaKita\Services\Domain\Product\DTO;

use TitaKita\DataTransferObjects\BaseDTO;
use TitaKita\DomainObjects\CapacityAssignmentDomainObject;
use Illuminate\Support\Collection;

class AvailableProductQuantitiesResponseDTO extends BaseDTO
{
    public function __construct(
        /** @var Collection<AvailableProductQuantitiesDTO> */
        public Collection  $productQuantities,
        /** @var Collection<CapacityAssignmentDomainObject> */
        public ?Collection $capacities = null,
    )
    {
    }
}
