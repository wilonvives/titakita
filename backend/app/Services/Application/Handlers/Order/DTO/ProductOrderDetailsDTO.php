<?php

namespace TitaKita\Services\Application\Handlers\Order\DTO;

use TitaKita\DataTransferObjects\Attributes\CollectionOf;
use TitaKita\DataTransferObjects\BaseDTO;
use TitaKita\Services\Domain\Product\DTO\OrderProductPriceDTO;
use Illuminate\Support\Collection;

class ProductOrderDetailsDTO extends BaseDTO
{
    public function __construct(
        public readonly int $product_id,
        #[CollectionOf(OrderProductPriceDTO::class)]
        public Collection   $quantities,
    )
    {
    }
}
