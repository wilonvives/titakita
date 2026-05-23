<?php

namespace TitaKita\Services\Application\Handlers\Order\DTO;

use TitaKita\DataTransferObjects\BaseDTO;

class GetOrderInvoiceDTO extends BaseDTO
{
    public function __construct(
        public readonly int $orderId,
        public readonly int $eventId,
    )
    {
    }
}
