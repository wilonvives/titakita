<?php

namespace TitaKita\Services\Domain\Event\DTO;

use TitaKita\DataTransferObjects\BaseDTO;

class EventCheckInStatsResponseDTO extends BaseDTO
{
    public function __construct(
        public readonly int $total_checked_in_attendees,
        public readonly int $total_attendees,
    )
    {
    }
}
