<?php

namespace TitaKita\Services\Application\Handlers\Order\DTO;

use TitaKita\DataTransferObjects\BaseDTO;

class MarkOrderAsPaidDTO extends BaseDTO
{
    public function __construct(
        public readonly int $eventId,
        public readonly int $orderId,
    )
    {
    }
}
