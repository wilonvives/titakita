<?php

declare(strict_types=1);

namespace TitaKita\Services\Application\Handlers\Booking;

use TitaKita\Services\Application\Handlers\Booking\DTO\BookingScheduleResultDTO;
use TitaKita\Services\Domain\Booking\BookingScheduleReader;
use TitaKita\Services\Domain\Booking\BookingSessionService;
use TitaKita\Services\Domain\Booking\Exception\SessionHasBookingsException;
use TitaKita\Services\Domain\Booking\Exception\SessionNotFoundException;

class DeleteBookingSessionHandler
{
    public function __construct(
        private readonly BookingSessionService $bookingSessionService,
        private readonly BookingScheduleReader $reader,
    ) {}

    /**
     * @throws SessionNotFoundException
     * @throws SessionHasBookingsException
     */
    public function handle(int $eventId, int $productId): BookingScheduleResultDTO
    {
        $this->bookingSessionService->deleteSession($eventId, $productId);

        return $this->reader->read($eventId);
    }
}
