<?php

namespace TitaKita\Services\Application\Handlers\Order\DTO;

use TitaKita\DataTransferObjects\BaseDTO;

class CreatedProductDataDTO extends BaseDTO
{
    public function __construct(
        public readonly CompleteOrderProductDataDTO $productRequestData,
        public readonly ?string                      $shortId,
    )
    {
    }
}
