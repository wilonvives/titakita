<?php

namespace Tests\Unit\Services\Domain\Booking;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Tests\TestCase;
use TitaKita\DomainObjects\Enums\EventType;
use TitaKita\DomainObjects\Generated\ProductCategoryDomainObjectAbstract;
use TitaKita\DomainObjects\Generated\ProductDomainObjectAbstract;
use TitaKita\DomainObjects\Generated\ScheduleDomainObjectAbstract;
use TitaKita\DomainObjects\ProductDomainObject;
use TitaKita\DomainObjects\ProductPriceDomainObject;
use TitaKita\Repository\Eloquent\Value\OrderAndDirection;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\ProductCategoryRepositoryInterface;
use TitaKita\Repository\Interfaces\ProductPriceRepositoryInterface;
use TitaKita\Repository\Interfaces\ProductRepositoryInterface;
use TitaKita\Repository\Interfaces\ScheduleRepositoryInterface;
use TitaKita\Services\Domain\Booking\BookingSessionService;
use TitaKita\Services\Domain\Booking\Exception\SessionHasBookingsException;
use TitaKita\Services\Domain\Booking\Exception\SessionNotFoundException;

class BookingSessionServiceTest extends TestCase
{
    use DatabaseTransactions;

    private function eventId(): int
    {
        $event = app(EventRepositoryInterface::class)->findFirstWhere([]);
        $this->assertNotNull($event, 'Expected at least one seeded event to exist.');

        return $event->getId();
    }

    private function eventTimezone(int $eventId): string
    {
        return app(EventRepositoryInterface::class)->findById($eventId)->getTimezone();
    }

    private function defaultCategoryId(int $eventId): int
    {
        return app(ProductCategoryRepositoryInterface::class)
            ->findWhere([ProductCategoryDomainObjectAbstract::EVENT_ID => $eventId])
            ->sortBy(fn ($category) => $category->getOrder())
            ->first()
            ->getId();
    }

    private function service(): BookingSessionService
    {
        return app(BookingSessionService::class);
    }

    private function scheduleId(int $eventId): int
    {
        return app(ScheduleRepositoryInterface::class)
            ->findFirstWhere([ScheduleDomainObjectAbstract::EVENT_ID => $eventId])
            ->getId();
    }

    /**
     * @return Collection<ProductDomainObject>
     */
    private function sessionProducts(int $scheduleId): Collection
    {
        return app(ProductRepositoryInterface::class)
            ->loadRelation(ProductPriceDomainObject::class)
            ->findWhere(
                [ProductDomainObjectAbstract::SCHEDULE_ID => $scheduleId],
                ['*'],
                [new OrderAndDirection(order: ProductDomainObjectAbstract::SESSION_START_AT, direction: 'asc')],
            );
    }

    public function test_create_sessions_single_creates_one_product(): void
    {
        $eventId = $this->eventId();
        $tz = $this->eventTimezone($eventId);
        $expectedCategoryId = $this->defaultCategoryId($eventId);

        $this->service()->createSessions(
            eventId: $eventId,
            date: '2026-06-06',
            startTime: '10:00',
            durationMinutes: 90,
            capacity: 8,
            description: 'Perfume workshop',
            repeatWeekly: false,
        );

        $products = $this->sessionProducts($this->scheduleId($eventId));
        $this->assertCount(1, $products);

        $product = $products->first();
        $this->assertSame('TICKET', $product->getProductType());
        $this->assertSame('FREE', $product->getType());
        $this->assertSame($expectedCategoryId, $product->getProductCategoryId());
        $this->assertSame('Perfume workshop', $product->getDescription());

        $start = Carbon::parse($product->getSessionStartAt(), 'UTC')->setTimezone($tz);
        $end = Carbon::parse($product->getSessionEndAt(), 'UTC')->setTimezone($tz);

        $this->assertSame('2026-06-06', $start->format('Y-m-d'));
        $this->assertSame('10:00', $start->format('H:i'));
        $this->assertSame('11:30', $end->format('H:i'));

        $prices = $product->getProductPrices();
        $this->assertCount(1, $prices);
        $this->assertSame(0.0, $prices->first()->getPrice());
        $this->assertSame(8, $prices->first()->getInitialQuantityAvailable());
    }

    public function test_create_sessions_repeat_weekly_generates_weekly_until_three_months(): void
    {
        $eventId = $this->eventId();
        $tz = $this->eventTimezone($eventId);

        $date = '2026-06-06';

        $start = Carbon::parse($date);
        $end = $start->copy()->addMonthsNoOverflow(3);
        $expectedCount = 0;
        for ($cursor = $start->copy(); $cursor->lessThanOrEqualTo($end); $cursor->addDays(7)) {
            $expectedCount++;
        }

        $this->service()->createSessions(
            eventId: $eventId,
            date: $date,
            startTime: '14:00',
            durationMinutes: 60,
            capacity: 5,
            description: null,
            repeatWeekly: true,
        );

        $products = $this->sessionProducts($this->scheduleId($eventId));
        $this->assertCount($expectedCount, $products);

        $times = $products
            ->map(fn (ProductDomainObject $p) => Carbon::parse($p->getSessionStartAt(), 'UTC')->setTimezone($tz)->format('H:i'))
            ->unique()
            ->values()
            ->all();
        $this->assertSame(['14:00'], $times);
    }

    public function test_create_sessions_dedupes_same_date_time(): void
    {
        $eventId = $this->eventId();

        $this->service()->createSessions(
            eventId: $eventId,
            date: '2026-06-06',
            startTime: '10:00',
            durationMinutes: 90,
            capacity: 8,
            description: null,
            repeatWeekly: false,
        );

        $this->service()->createSessions(
            eventId: $eventId,
            date: '2026-06-06',
            startTime: '10:00',
            durationMinutes: 90,
            capacity: 8,
            description: null,
            repeatWeekly: false,
        );

        $products = $this->sessionProducts($this->scheduleId($eventId));
        $this->assertCount(1, $products);
    }

    public function test_update_session_changes_start_end_capacity_description(): void
    {
        $eventId = $this->eventId();
        $tz = $this->eventTimezone($eventId);

        $this->service()->createSessions(
            eventId: $eventId,
            date: '2026-06-06',
            startTime: '10:00',
            durationMinutes: 90,
            capacity: 8,
            description: 'Original',
            repeatWeekly: false,
        );

        $product = $this->sessionProducts($this->scheduleId($eventId))->first();

        $this->service()->updateSession(
            eventId: $eventId,
            productId: $product->getId(),
            date: '2026-06-07',
            startTime: '15:30',
            durationMinutes: 120,
            capacity: 4,
            description: 'Updated',
        );

        $updated = $this->sessionProducts($this->scheduleId($eventId))->first();

        $start = Carbon::parse($updated->getSessionStartAt(), 'UTC')->setTimezone($tz);
        $end = Carbon::parse($updated->getSessionEndAt(), 'UTC')->setTimezone($tz);

        $this->assertSame('2026-06-07', $start->format('Y-m-d'));
        $this->assertSame('15:30', $start->format('H:i'));
        $this->assertSame('17:30', $end->format('H:i'));
        $this->assertSame('Updated', $updated->getDescription());
        $this->assertSame(4, $updated->getProductPrices()->first()->getInitialQuantityAvailable());
    }

    public function test_delete_session_unbooked_removes_it(): void
    {
        $eventId = $this->eventId();

        $this->service()->createSessions(
            eventId: $eventId,
            date: '2026-06-06',
            startTime: '10:00',
            durationMinutes: 90,
            capacity: 8,
            description: null,
            repeatWeekly: false,
        );

        $product = $this->sessionProducts($this->scheduleId($eventId))->first();

        $this->service()->deleteSession($eventId, $product->getId());

        $this->assertCount(0, $this->sessionProducts($this->scheduleId($eventId)));
    }

    public function test_delete_session_booked_throws(): void
    {
        $eventId = $this->eventId();

        $this->service()->createSessions(
            eventId: $eventId,
            date: '2026-06-06',
            startTime: '10:00',
            durationMinutes: 90,
            capacity: 8,
            description: null,
            repeatWeekly: false,
        );

        $product = $this->sessionProducts($this->scheduleId($eventId))->first();

        app(ProductPriceRepositoryInterface::class)->updateWhere(
            ['quantity_sold' => 1],
            ['product_id' => $product->getId()],
        );

        $this->expectException(SessionHasBookingsException::class);
        $this->service()->deleteSession($eventId, $product->getId());
    }

    public function test_update_session_throws_for_non_session_product(): void
    {
        $eventId = $this->eventId();

        $this->expectException(SessionNotFoundException::class);
        $this->service()->updateSession(
            eventId: $eventId,
            productId: 999999999,
            date: '2026-06-06',
            startTime: '10:00',
            durationMinutes: 90,
            capacity: 8,
            description: null,
        );
    }

    public function test_ensure_schedule_sets_event_type_booking_and_stores_defaults(): void
    {
        $eventId = $this->eventId();

        $schedule = $this->service()->ensureSchedule($eventId, 75, 12);

        $this->assertSame(75, $schedule->getSessionDurationMinutes());
        $this->assertSame(12, $schedule->getCapacityPerSession());

        $event = app(EventRepositoryInterface::class)->findById($eventId);
        $this->assertSame(EventType::BOOKING->value, $event->getEventType());

        // Calling again with new defaults updates the same row.
        $again = $this->service()->ensureSchedule($eventId, 30, null);
        $this->assertSame($schedule->getId(), $again->getId());
        $this->assertSame(30, $again->getSessionDurationMinutes());
        $this->assertNull($again->getCapacityPerSession());
    }
}
