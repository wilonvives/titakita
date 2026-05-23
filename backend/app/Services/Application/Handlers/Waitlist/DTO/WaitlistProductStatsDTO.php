<?php

namespace TitaKita\Services\Application\Handlers\Waitlist\DTO;

use TitaKita\DataTransferObjects\BaseDataObject;

class WaitlistProductStatsDTO extends BaseDataObject
{
    public function __construct(
        public int $product_price_id,
        public string $product_title,
        public int $waiting,
        public int $offered,
        public ?int $available,
    )
    {
    }
}
