<?php

declare(strict_types=1);

namespace TitaKita\Services\Application\Handlers\Booking;

use TitaKita\Services\Application\Handlers\Booking\DTO\BookingScheduleResultDTO;
use TitaKita\Services\Domain\Booking\BookingScheduleReader;

class GetScheduleHandler
{
    public function __construct(
        private readonly BookingScheduleReader $reader,
    ) {}

    public function handle(int $eventId): BookingScheduleResultDTO
    {
        return $this->reader->read($eventId);
    }
}
