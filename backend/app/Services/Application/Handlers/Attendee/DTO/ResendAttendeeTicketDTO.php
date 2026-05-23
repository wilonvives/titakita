<?php

namespace TitaKita\Services\Application\Handlers\Attendee\DTO;

use TitaKita\DataTransferObjects\BaseDTO;

class ResendAttendeeTicketDTO extends BaseDTO
{
    public function __construct(
        public int $attendeeId,
        public int $eventId,
    )
    {
    }
}
