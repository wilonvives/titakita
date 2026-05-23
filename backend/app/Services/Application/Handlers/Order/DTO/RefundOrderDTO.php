<?php

namespace TitaKita\Services\Application\Handlers\Order\DTO;

use TitaKita\DataTransferObjects\BaseDTO;

class RefundOrderDTO extends BaseDTO
{
    public function __construct(
        public readonly int   $event_id,
        public readonly int   $order_id,
        public readonly float $amount,
        public readonly bool  $notify_buyer,
        public readonly bool  $cancel_order
    )
    {
    }
}
