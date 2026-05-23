<?php

namespace Tests\Unit\Services\Application\Handlers\Booking;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use TitaKita\DomainObjects\Enums\ScheduleScopeType;
use TitaKita\DomainObjects\ScheduleDomainObject;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Services\Application\Handlers\Booking\DTO\UpsertScheduleDTO;
use TitaKita\Services\Application\Handlers\Booking\GetScheduleHandler;
use TitaKita\Services\Application\Handlers\Booking\UpsertScheduleHandler;

class GetScheduleHandlerTest extends TestCase
{
    use DatabaseTransactions;

    private function eventId(): int
    {
        $event = app(EventRepositoryInterface::class)->findFirstWhere([]);
        $this->assertNotNull($event, 'Expected at least one seeded event to exist.');

        return $event->getId();
    }

    public function test_returns_null_when_no_schedule_exists(): void
    {
        $eventId = $this->eventId();

        $schedule = app(GetScheduleHandler::class)->handle($eventId);

        $this->assertNull($schedule);
    }

    public function test_returns_schedule_when_one_exists(): void
    {
        $eventId = $this->eventId();

        app(UpsertScheduleHandler::class)->handle(UpsertScheduleDTO::from([
            'event_id' => $eventId,
            'session_duration_minutes' => 90,
            'start_times' => ['09:00'],
            'capacity_per_session' => 4,
            'scope_type' => ScheduleScopeType::SINGLE_DAY,
            'range_start_date' => '2026-06-06',
        ]));

        $schedule = app(GetScheduleHandler::class)->handle($eventId);

        $this->assertInstanceOf(ScheduleDomainObject::class, $schedule);
        $this->assertSame($eventId, $schedule->getEventId());
        $this->assertSame(90, $schedule->getSessionDurationMinutes());
        $this->assertSame(['09:00'], $schedule->getStartTimes());
    }
}
