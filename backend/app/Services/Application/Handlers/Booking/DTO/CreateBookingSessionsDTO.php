<?php

declare(strict_types=1);

namespace TitaKita\Services\Application\Handlers\Booking\DTO;

use TitaKita\DataTransferObjects\BaseDataObject;

class CreateBookingSessionsDTO extends BaseDataObject
{
    public function __construct(
        public int $event_id,
        public string $session_date,
        public string $start_time,
        public int $duration_minutes,
        public ?int $capacity = null,
        public ?string $description = null,
        public bool $repeat_weekly = false,
    ) {}
}
