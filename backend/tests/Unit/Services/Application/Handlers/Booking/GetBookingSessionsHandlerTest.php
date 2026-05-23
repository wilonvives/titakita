<?php

namespace Tests\Unit\Services\Application\Handlers\Booking;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use TitaKita\DomainObjects\Enums\ScheduleScopeType;
use TitaKita\DomainObjects\Generated\ProductDomainObjectAbstract;
use TitaKita\DomainObjects\ProductDomainObject;
use TitaKita\DomainObjects\ProductPriceDomainObject;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\ProductPriceRepositoryInterface;
use TitaKita\Repository\Interfaces\ProductRepositoryInterface;
use TitaKita\Services\Application\Handlers\Booking\DTO\UpsertScheduleDTO;
use TitaKita\Services\Application\Handlers\Booking\GetBookingSessionsHandler;
use TitaKita\Services\Application\Handlers\Booking\UpsertScheduleHandler;

class GetBookingSessionsHandlerTest extends TestCase
{
    use DatabaseTransactions;

    private function eventId(): int
    {
        $event = app(EventRepositoryInterface::class)->findFirstWhere([]);
        $this->assertNotNull($event, 'Expected at least one seeded event to exist.');

        return $event->getId();
    }

    private function futureDate(): string
    {
        return Carbon::now()->addMonth()->format('Y-m-d');
    }

    public function test_groups_future_sessions_by_date_with_remaining_capacity(): void
    {
        $eventId = $this->eventId();
        $date = $this->futureDate();

        app(UpsertScheduleHandler::class)->handle(UpsertScheduleDTO::from([
            'event_id' => $eventId,
            'session_duration_minutes' => 120,
            'start_times' => ['10:00', '13:00'],
            'capacity_per_session' => 8,
            'scope_type' => ScheduleScopeType::SINGLE_DAY,
            'range_start_date' => $date,
        ]));

        $groups = app(GetBookingSessionsHandler::class)->handle($eventId);

        $this->assertCount(1, $groups, 'Expected a single date group.');
        $group = $groups->first();
        $this->assertSame($date, $group['date']);
        $this->assertCount(2, $group['sessions']);

        $first = $group['sessions'][0];
        $this->assertArrayHasKey('product_id', $first);
        $this->assertArrayHasKey('session_start_at', $first);
        $this->assertArrayHasKey('session_end_at', $first);
        $this->assertSame(8, $first['capacity_remaining']);
        $this->assertFalse($first['is_sold_out']);
    }

    public function test_excludes_past_sessions(): void
    {
        $eventId = $this->eventId();

        app(UpsertScheduleHandler::class)->handle(UpsertScheduleDTO::from([
            'event_id' => $eventId,
            'session_duration_minutes' => 60,
            'start_times' => ['10:00'],
            'capacity_per_session' => 5,
            'scope_type' => ScheduleScopeType::SINGLE_DAY,
            'range_start_date' => Carbon::now()->subMonth()->format('Y-m-d'),
        ]));

        $groups = app(GetBookingSessionsHandler::class)->handle($eventId);

        $this->assertCount(0, $groups);
    }

    public function test_marks_sold_out_session(): void
    {
        $eventId = $this->eventId();
        $date = $this->futureDate();

        app(UpsertScheduleHandler::class)->handle(UpsertScheduleDTO::from([
            'event_id' => $eventId,
            'session_duration_minutes' => 60,
            'start_times' => ['11:00'],
            'capacity_per_session' => 2,
            'scope_type' => ScheduleScopeType::SINGLE_DAY,
            'range_start_date' => $date,
        ]));

        /** @var ProductDomainObject $product */
        $product = app(ProductRepositoryInterface::class)
            ->loadRelation(ProductPriceDomainObject::class)
            ->findWhere([ProductDomainObjectAbstract::EVENT_ID => $eventId])
            ->first(fn (ProductDomainObject $p) => $p->getScheduleId() !== null);

        $this->assertNotNull($product);
        $priceId = $product->getProductPrices()->first()->getId();

        app(ProductPriceRepositoryInterface::class)->updateWhere(
            ['quantity_sold' => 2],
            ['id' => $priceId],
        );

        $groups = app(GetBookingSessionsHandler::class)->handle($eventId);

        $session = $groups->first()['sessions'][0];
        $this->assertSame(0, $session['capacity_remaining']);
        $this->assertTrue($session['is_sold_out']);
    }
}
