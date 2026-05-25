<?php

declare(strict_types=1);

namespace TitaKita\Services\Application\Handlers\Booking;

use Throwable;
use TitaKita\Services\Application\Handlers\Booking\DTO\BookingScheduleResultDTO;
use TitaKita\Services\Application\Handlers\Booking\DTO\CreateBookingSessionsDTO;
use TitaKita\Services\Domain\Booking\BookingScheduleReader;
use TitaKita\Services\Domain\Booking\BookingSessionService;

class CreateBookingSessionsHandler
{
    public function __construct(
        private readonly BookingSessionService $bookingSessionService,
        private readonly BookingScheduleReader $reader,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(CreateBookingSessionsDTO $dto): BookingScheduleResultDTO
    {
        $this->bookingSessionService->createSessions(
            eventId: $dto->event_id,
            date: $dto->session_date,
            startTime: $dto->start_time,
            durationMinutes: $dto->duration_minutes,
            capacity: $dto->capacity,
            description: $dto->description,
            repeatWeekly: $dto->repeat_weekly,
            price: $dto->price,
        );

        return $this->reader->read($dto->event_id);
    }
}
