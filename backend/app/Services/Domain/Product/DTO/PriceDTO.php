<?php

namespace TitaKita\Services\Domain\Product\DTO;

use TitaKita\DataTransferObjects\BaseDTO;

class PriceDTO extends BaseDTO
{
    public function __construct(
        public float $price,
        public ?float $price_before_discount = null,
    )
    {
    }
}
