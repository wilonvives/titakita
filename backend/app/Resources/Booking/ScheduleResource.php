<?php

namespace TitaKita\Resources\Booking;

use Illuminate\Http\Request;
use TitaKita\DomainObjects\ScheduleDomainObject;
use TitaKita\Resources\BaseResource;
use TitaKita\Services\Application\Handlers\Booking\DTO\ScheduleResultDTO;

/**
 * @mixin ScheduleResultDTO
 */
class ScheduleResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        /** @var ScheduleDomainObject $schedule */
        $schedule = $this->schedule;

        return [
            'id' => $schedule->getId(),
            'event_id' => $schedule->getEventId(),
            'session_duration_minutes' => $schedule->getSessionDurationMinutes(),
            'start_times' => $schedule->getStartTimes(),
            'capacity_per_session' => $schedule->getCapacityPerSession(),
            'scope_type' => $schedule->getScopeType(),
            'weekdays' => $schedule->getWeekdays(),
            'range_start_date' => $schedule->getRangeStartDate(),
            'range_end_date' => $schedule->getRangeEndDate(),
            'specific_dates' => $schedule->getSpecificDates(),
            'generated_session_count' => $this->generatedSessionCount,
        ];
    }
}
