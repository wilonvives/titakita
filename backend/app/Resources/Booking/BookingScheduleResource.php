<?php

namespace TitaKita\Resources\Booking;

use Illuminate\Http\Request;
use TitaKita\Resources\BaseResource;
use TitaKita\Services\Application\Handlers\Booking\DTO\BookingScheduleResultDTO;

/**
 * @mixin BookingScheduleResultDTO
 */
class BookingScheduleResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        /** @var BookingScheduleResultDTO $result */
        $result = $this->resource;

        $schedule = $result->schedule;

        return [
            'id' => $schedule?->getId(),
            'event_id' => $result->eventId,
            'session_duration_minutes' => $schedule?->getSessionDurationMinutes(),
            'capacity_per_session' => $schedule?->getCapacityPerSession(),
            'default_price' => $schedule?->getDefaultPrice(),
            'sessions' => BookingSessionResource::collection($result->sessions),
        ];
    }
}
