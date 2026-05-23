<?php

namespace TitaKita\Services\Domain\Product\DTO;

use TitaKita\DataTransferObjects\BaseDTO;
use TitaKita\DomainObjects\CapacityAssignmentDomainObject;
use Illuminate\Support\Collection;

class AvailableProductQuantitiesDTO extends BaseDTO
{
    public function __construct(
        public int         $product_id,
        public int         $price_id,
        public string      $product_title,
        public ?string     $price_label,
        public int         $quantity_available,
        public int         $quantity_reserved,
        public ?int        $initial_quantity_available,
        /** @var Collection<CapacityAssignmentDomainObject> */
        public ?Collection $capacities = null,
    )
    {
    }
}
