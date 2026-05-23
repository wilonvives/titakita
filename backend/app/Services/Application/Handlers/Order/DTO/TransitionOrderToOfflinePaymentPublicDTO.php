<?php

namespace TitaKita\Services\Application\Handlers\Order\DTO;

use TitaKita\DataTransferObjects\BaseDTO;

class TransitionOrderToOfflinePaymentPublicDTO extends BaseDTO
{
    public function __construct(
        public readonly string $orderShortId,
    )
    {
    }
}
