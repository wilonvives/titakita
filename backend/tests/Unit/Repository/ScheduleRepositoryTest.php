<?php

namespace Tests\Unit\Repository;

use TitaKita\DomainObjects\Enums\ScheduleScopeType;
use TitaKita\DomainObjects\ScheduleDomainObject;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\ScheduleRepositoryInterface;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ScheduleRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    private function eventId(): int
    {
        $event = app(EventRepositoryInterface::class)->findFirstWhere([]);
        $this->assertNotNull($event, 'Expected at least one seeded event to exist.');

        return $event->getId();
    }

    public function test_create_and_read_schedule_round_trips_all_fields(): void
    {
        /** @var ScheduleRepositoryInterface $repository */
        $repository = app(ScheduleRepositoryInterface::class);

        $created = $repository->create([
            'event_id' => $this->eventId(),
            'session_duration_minutes' => 120,
            'start_times' => ['10:00', '13:00', '15:30'],
            'capacity_per_session' => 8,
            'scope_type' => ScheduleScopeType::RECURRING_WEEKLY->value,
            'weekdays' => ['SATURDAY', 'SUNDAY'],
            'range_start_date' => '2026-06-06',
            'range_end_date' => '2026-07-06',
            'specific_dates' => null,
        ]);

        $this->assertInstanceOf(ScheduleDomainObject::class, $created);

        /** @var ScheduleDomainObject $schedule */
        $schedule = $repository->findById($created->getId());

        $this->assertSame($this->eventId(), $schedule->getEventId());
        $this->assertSame(120, $schedule->getSessionDurationMinutes());
        $this->assertSame(['10:00', '13:00', '15:30'], $schedule->getStartTimes());
        $this->assertSame(8, $schedule->getCapacityPerSession());
        $this->assertSame(ScheduleScopeType::RECURRING_WEEKLY->value, $schedule->getScopeType());
        $this->assertSame(['SATURDAY', 'SUNDAY'], $schedule->getWeekdays());
        $this->assertStringStartsWith('2026-06-06', $schedule->getRangeStartDate());
        $this->assertStringStartsWith('2026-07-06', $schedule->getRangeEndDate());
        $this->assertNull($schedule->getSpecificDates());
    }

    public function test_capacity_per_session_can_be_null_for_unlimited(): void
    {
        /** @var ScheduleRepositoryInterface $repository */
        $repository = app(ScheduleRepositoryInterface::class);

        $created = $repository->create([
            'event_id' => $this->eventId(),
            'session_duration_minutes' => 60,
            'start_times' => ['09:00'],
            'capacity_per_session' => null,
            'scope_type' => ScheduleScopeType::SPECIFIC_DATES->value,
            'specific_dates' => ['2026-06-10', '2026-06-12'],
        ]);

        /** @var ScheduleDomainObject $schedule */
        $schedule = $repository->findById($created->getId());

        $this->assertNull($schedule->getCapacityPerSession());
        $this->assertSame(ScheduleScopeType::SPECIFIC_DATES->value, $schedule->getScopeType());
        $this->assertSame(['2026-06-10', '2026-06-12'], $schedule->getSpecificDates());
        $this->assertNull($schedule->getWeekdays());
    }
}
