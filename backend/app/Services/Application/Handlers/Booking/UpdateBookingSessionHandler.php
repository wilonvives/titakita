<?php

declare(strict_types=1);

namespace TitaKita\Services\Application\Handlers\Booking;

use TitaKita\Services\Application\Handlers\Booking\DTO\BookingScheduleResultDTO;
use TitaKita\Services\Application\Handlers\Booking\DTO\UpdateBookingSessionDTO;
use TitaKita\Services\Domain\Booking\BookingScheduleReader;
use TitaKita\Services\Domain\Booking\BookingSessionService;
use TitaKita\Services\Domain\Booking\Exception\SessionNotFoundException;

class UpdateBookingSessionHandler
{
    public function __construct(
        private readonly BookingSessionService $bookingSessionService,
        private readonly BookingScheduleReader $reader,
    ) {}

    /**
     * @throws SessionNotFoundException
     */
    public function handle(UpdateBookingSessionDTO $dto): BookingScheduleResultDTO
    {
        $this->bookingSessionService->updateSession(
            eventId: $dto->event_id,
            productId: $dto->product_id,
            date: $dto->session_date,
            startTime: $dto->start_time,
            durationMinutes: $dto->duration_minutes,
            capacity: $dto->capacity,
            description: $dto->description,
            price: $dto->price,
        );

        return $this->reader->read($dto->event_id);
    }
}
