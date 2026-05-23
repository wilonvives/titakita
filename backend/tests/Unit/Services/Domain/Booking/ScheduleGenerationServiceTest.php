<?php

namespace Tests\Unit\Services\Domain\Booking;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Tests\TestCase;
use TitaKita\DomainObjects\Enums\ScheduleScopeType;
use TitaKita\DomainObjects\Generated\ProductDomainObjectAbstract;
use TitaKita\DomainObjects\ProductDomainObject;
use TitaKita\DomainObjects\ProductPriceDomainObject;
use TitaKita\DomainObjects\ScheduleDomainObject;
use TitaKita\Repository\Eloquent\Value\OrderAndDirection;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\ProductRepositoryInterface;
use TitaKita\Repository\Interfaces\ScheduleRepositoryInterface;
use TitaKita\Services\Domain\Booking\Exception\ScheduleRangeTooLongException;
use TitaKita\Services\Domain\Booking\ScheduleGenerationService;

class ScheduleGenerationServiceTest extends TestCase
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

    private function service(): ScheduleGenerationService
    {
        return app(ScheduleGenerationService::class);
    }

    private function createSchedule(int $eventId, array $overrides): ScheduleDomainObject
    {
        return app(ScheduleRepositoryInterface::class)->create(array_merge([
            'event_id' => $eventId,
            'session_duration_minutes' => 120,
            'start_times' => ['10:00'],
            'capacity_per_session' => 8,
            'scope_type' => ScheduleScopeType::SINGLE_DAY->value,
        ], $overrides));
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

    public function test_single_day_generates_one_product_per_start_time(): void
    {
        $eventId = $this->eventId();
        $tz = $this->eventTimezone($eventId);

        $schedule = $this->createSchedule($eventId, [
            'session_duration_minutes' => 120,
            'start_times' => ['10:00', '13:00', '15:30'],
            'capacity_per_session' => 8,
            'scope_type' => ScheduleScopeType::SINGLE_DAY->value,
            'range_start_date' => '2026-06-06',
        ]);

        $this->service()->generate($schedule);

        $products = $this->sessionProducts($schedule->getId());

        $this->assertCount(3, $products);

        $expected = [
            ['10:00', '12:00'],
            ['13:00', '15:00'],
            ['15:30', '17:30'],
        ];

        foreach ($products as $index => $product) {
            $this->assertSame('TICKET', $product->getProductType());
            $this->assertSame('FREE', $product->getType());
            $this->assertSame($schedule->getId(), $product->getScheduleId());

            $start = Carbon::parse($product->getSessionStartAt(), 'UTC')->setTimezone($tz);
            $end = Carbon::parse($product->getSessionEndAt(), 'UTC')->setTimezone($tz);

            $this->assertSame('2026-06-06', $start->format('Y-m-d'));
            $this->assertSame($expected[$index][0], $start->format('H:i'));
            $this->assertSame($expected[$index][1], $end->format('H:i'));

            $prices = $product->getProductPrices();
            $this->assertCount(1, $prices);
            $this->assertSame(0.0, $prices->first()->getPrice());
            $this->assertSame(8, $prices->first()->getInitialQuantityAvailable());
        }
    }

    public function test_single_day_unlimited_capacity_when_null(): void
    {
        $eventId = $this->eventId();

        $schedule = $this->createSchedule($eventId, [
            'start_times' => ['10:00'],
            'capacity_per_session' => null,
            'scope_type' => ScheduleScopeType::SINGLE_DAY->value,
            'range_start_date' => '2026-06-06',
        ]);

        $this->service()->generate($schedule);

        $products = $this->sessionProducts($schedule->getId());
        $this->assertCount(1, $products);
        $this->assertNull($products->first()->getProductPrices()->first()->getInitialQuantityAvailable());
    }

    public function test_recurring_weekly_generates_matching_days_times(): void
    {
        $eventId = $this->eventId();
        $tz = $this->eventTimezone($eventId);

        // 2026-06-06 is a Saturday. Four-week window: 06-06..2026-07-03 inclusive.
        // Saturdays: 06-06, 06-13, 06-20, 06-27 (4). Sundays: 06-07,06-14,06-21,06-28 (4). => 8 days x 2 times = 16.
        $schedule = $this->createSchedule($eventId, [
            'start_times' => ['10:00', '14:00'],
            'capacity_per_session' => 5,
            'scope_type' => ScheduleScopeType::RECURRING_WEEKLY->value,
            'weekdays' => [6, 0], // Saturday, Sunday (Carbon dayOfWeek: Sun=0..Sat=6)
            'range_start_date' => '2026-06-06',
            'range_end_date' => '2026-07-03',
        ]);

        $this->service()->generate($schedule);

        $products = $this->sessionProducts($schedule->getId());
        $this->assertCount(16, $products);

        $dates = $products
            ->map(fn (ProductDomainObject $p) => Carbon::parse($p->getSessionStartAt(), 'UTC')->setTimezone($tz)->format('Y-m-d'))
            ->unique()
            ->values()
            ->all();

        $this->assertSame([
            '2026-06-06', '2026-06-07',
            '2026-06-13', '2026-06-14',
            '2026-06-20', '2026-06-21',
            '2026-06-27', '2026-06-28',
        ], $dates);
    }

    public function test_specific_dates_generates_each_date_times(): void
    {
        $eventId = $this->eventId();
        $tz = $this->eventTimezone($eventId);

        $schedule = $this->createSchedule($eventId, [
            'start_times' => ['09:00', '11:00'],
            'capacity_per_session' => 4,
            'scope_type' => ScheduleScopeType::SPECIFIC_DATES->value,
            'specific_dates' => ['2026-06-10', '2026-08-20'],
        ]);

        $this->service()->generate($schedule);

        $products = $this->sessionProducts($schedule->getId());
        $this->assertCount(4, $products);

        $dates = $products
            ->map(fn (ProductDomainObject $p) => Carbon::parse($p->getSessionStartAt(), 'UTC')->setTimezone($tz)->format('Y-m-d'))
            ->unique()
            ->values()
            ->all();

        $this->assertSame(['2026-06-10', '2026-08-20'], $dates);
    }

    public function test_recurring_range_over_three_months_throws(): void
    {
        $eventId = $this->eventId();

        $schedule = $this->createSchedule($eventId, [
            'start_times' => ['10:00'],
            'scope_type' => ScheduleScopeType::RECURRING_WEEKLY->value,
            'weekdays' => [6],
            'range_start_date' => '2026-06-06',
            'range_end_date' => '2026-09-30', // > 3 months
        ]);

        $this->expectException(ScheduleRangeTooLongException::class);
        $this->service()->generate($schedule);
    }
}
