<?php

namespace Tests\Unit\Services\Application\Handlers\Booking;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use TitaKita\DomainObjects\Enums\EventType;
use TitaKita\DomainObjects\Enums\ScheduleScopeType;
use TitaKita\DomainObjects\Generated\ProductDomainObjectAbstract;
use TitaKita\DomainObjects\ScheduleDomainObject;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\ProductRepositoryInterface;
use TitaKita\Services\Application\Handlers\Booking\DTO\UpsertScheduleDTO;
use TitaKita\Services\Application\Handlers\Booking\UpsertScheduleHandler;

class UpsertScheduleHandlerTest extends TestCase
{
    use DatabaseTransactions;

    private function eventId(): int
    {
        $event = app(EventRepositoryInterface::class)->findFirstWhere([]);
        $this->assertNotNull($event, 'Expected at least one seeded event to exist.');

        return $event->getId();
    }

    private function handler(): UpsertScheduleHandler
    {
        return app(UpsertScheduleHandler::class);
    }

    public function test_create_persists_schedule_flips_event_type_and_generates_sessions(): void
    {
        $eventId = $this->eventId();

        $dto = UpsertScheduleDTO::from([
            'event_id' => $eventId,
            'session_duration_minutes' => 120,
            'start_times' => ['10:00', '13:00', '15:30'],
            'capacity_per_session' => 8,
            'scope_type' => ScheduleScopeType::SINGLE_DAY,
            'range_start_date' => '2026-06-06',
        ]);

        $result = $this->handler()->handle($dto);

        $schedule = $result->schedule;
        $this->assertInstanceOf(ScheduleDomainObject::class, $schedule);
        $this->assertSame($eventId, $schedule->getEventId());
        $this->assertSame(['10:00', '13:00', '15:30'], $schedule->getStartTimes());
        $this->assertSame(3, $result->generatedSessionCount);

        $event = app(EventRepositoryInterface::class)->findById($eventId);
        $this->assertSame(EventType::BOOKING->value, $event->getEventType());

        $sessions = app(ProductRepositoryInterface::class)
            ->findWhere([ProductDomainObjectAbstract::SCHEDULE_ID => $schedule->getId()]);
        $this->assertCount(3, $sessions);
    }

    public function test_update_reconciles_existing_schedule_for_event(): void
    {
        $eventId = $this->eventId();

        $this->handler()->handle(UpsertScheduleDTO::from([
            'event_id' => $eventId,
            'session_duration_minutes' => 120,
            'start_times' => ['10:00'],
            'capacity_per_session' => 8,
            'scope_type' => ScheduleScopeType::SINGLE_DAY,
            'range_start_date' => '2026-06-06',
        ]));

        $result = $this->handler()->handle(UpsertScheduleDTO::from([
            'event_id' => $eventId,
            'session_duration_minutes' => 120,
            'start_times' => ['10:00', '14:00'],
            'capacity_per_session' => 8,
            'scope_type' => ScheduleScopeType::SINGLE_DAY,
            'range_start_date' => '2026-06-06',
        ]));

        $schedules = app(\TitaKita\Repository\Interfaces\ScheduleRepositoryInterface::class)
            ->findWhere([\TitaKita\DomainObjects\Generated\ScheduleDomainObjectAbstract::EVENT_ID => $eventId]);

        $this->assertCount(1, $schedules, 'Only one schedule should exist per event.');
        $this->assertSame(['10:00', '14:00'], $result->schedule->getStartTimes());
        $this->assertSame(2, $result->generatedSessionCount);
    }
}
