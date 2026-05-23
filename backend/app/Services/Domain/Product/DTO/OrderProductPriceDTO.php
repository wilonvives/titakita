<?php

namespace TitaKita\Services\Domain\Product\DTO;

use TitaKita\DataTransferObjects\BaseDTO;

class OrderProductPriceDTO extends BaseDTO
{
    public function __construct(
        public readonly int    $quantity,
        public readonly int    $price_id,
        public readonly ?float $price = null // used for donation products
    )
    {
    }
}
