<?php

namespace Tests\Unit\Services\Domain\Booking;

use Carbon\Carbon;
use TitaKita\DomainObjects\Enums\ScheduleScopeType;
use TitaKita\DomainObjects\Generated\ProductDomainObjectAbstract;
use TitaKita\DomainObjects\ProductPriceDomainObject;
use TitaKita\DomainObjects\ScheduleDomainObject;
use TitaKita\Repository\Eloquent\Value\OrderAndDirection;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\ProductRepositoryInterface;
use TitaKita\Repository\Interfaces\ScheduleRepositoryInterface;
use TitaKita\Services\Domain\Booking\ScheduleGenerationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Tests\TestCase;

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
}
