<?php

namespace TitaKita\Services\Application\Handlers\Waitlist\DTO;

use TitaKita\DataTransferObjects\BaseDataObject;

class WaitlistStatsDTO extends BaseDataObject
{
    public function __construct(
        public int $total,
        public int $waiting,
        public int $offered,
        public int $purchased,
        public int $cancelled,
        public int $expired,
        /** @var WaitlistProductStatsDTO[] */
        public array $products = [],
    )
    {
    }
}
