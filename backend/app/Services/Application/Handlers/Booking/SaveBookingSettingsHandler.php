<?php

declare(strict_types=1);

namespace TitaKita\Services\Application\Handlers\Booking;

use TitaKita\Services\Application\Handlers\Booking\DTO\BookingScheduleResultDTO;
use TitaKita\Services\Application\Handlers\Booking\DTO\SaveBookingSettingsDTO;
use TitaKita\Services\Domain\Booking\BookingScheduleReader;
use TitaKita\Services\Domain\Booking\BookingSessionService;

class SaveBookingSettingsHandler
{
    public function __construct(
        private readonly BookingSessionService $bookingSessionService,
        private readonly BookingScheduleReader $reader,
    ) {}

    public function handle(SaveBookingSettingsDTO $dto): BookingScheduleResultDTO
    {
        $this->bookingSessionService->ensureSchedule(
            eventId: $dto->event_id,
            defaultDurationMinutes: $dto->session_duration_minutes,
            defaultCapacity: $dto->capacity_per_session,
            defaultPrice: $dto->default_price,
        );

        return $this->reader->read($dto->event_id);
    }
}
