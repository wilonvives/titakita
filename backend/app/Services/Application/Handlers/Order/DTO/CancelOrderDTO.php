<?php

namespace TitaKita\Services\Application\Handlers\Order\DTO;

use TitaKita\DataTransferObjects\BaseDTO;

class CancelOrderDTO extends BaseDTO
{
    public function __construct(
        public int $eventId,
        public int $orderId,
        public bool $refund = false
    )
    {
    }
}
