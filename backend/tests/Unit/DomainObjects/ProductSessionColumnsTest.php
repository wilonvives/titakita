<?php

namespace Tests\Unit\DomainObjects;

use TitaKita\DomainObjects\Enums\ProductPriceType;
use TitaKita\DomainObjects\Enums\ProductType;
use TitaKita\DomainObjects\Enums\ScheduleScopeType;
use TitaKita\DomainObjects\ProductDomainObject;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\ProductRepositoryInterface;
use TitaKita\Repository\Interfaces\ScheduleRepositoryInterface;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ProductSessionColumnsTest extends TestCase
{
    use DatabaseTransactions;

    private function eventId(): int
    {
        $event = app(EventRepositoryInterface::class)->findFirstWhere([]);
        $this->assertNotNull($event, 'Expected at least one seeded event to exist.');

        return $event->getId();
    }

    public function test_product_persists_session_columns(): void
    {
        $eventId = $this->eventId();

        $schedule = app(ScheduleRepositoryInterface::class)->create([
            'event_id' => $eventId,
            'session_duration_minutes' => 120,
            'start_times' => ['10:00'],
            'capacity_per_session' => 8,
            'scope_type' => ScheduleScopeType::SINGLE_DAY->value,
            'range_start_date' => '2026-06-06',
        ]);

        /** @var ProductRepositoryInterface $repository */
        $repository = app(ProductRepositoryInterface::class);

        $created = $repository->create([
            'title' => 'Session 10:00-12:00',
            'event_id' => $eventId,
            'type' => ProductPriceType::FREE->name,
            'product_type' => ProductType::TICKET->name,
            'order' => 1,
            'schedule_id' => $schedule->getId(),
            'session_start_at' => '2026-06-06 10:00:00',
            'session_end_at' => '2026-06-06 12:00:00',
        ]);

        /** @var ProductDomainObject $product */
        $product = $repository->findById($created->getId());

        $this->assertSame($schedule->getId(), $product->getScheduleId());
        $this->assertStringStartsWith('2026-06-06T10:00:00', $product->getSessionStartAt());
        $this->assertStringStartsWith('2026-06-06T12:00:00', $product->getSessionEndAt());
    }

    public function test_normal_product_has_null_session_columns_and_unchanged_behaviour(): void
    {
        $eventId = $this->eventId();

        /** @var ProductRepositoryInterface $repository */
        $repository = app(ProductRepositoryInterface::class);

        $created = $repository->create([
            'title' => 'Regular Ticket',
            'event_id' => $eventId,
            'type' => ProductPriceType::FREE->name,
            'product_type' => ProductType::TICKET->name,
            'order' => 1,
        ]);

        /** @var ProductDomainObject $product */
        $product = $repository->findById($created->getId());

        $this->assertNull($product->getScheduleId());
        $this->assertNull($product->getSessionStartAt());
        $this->assertNull($product->getSessionEndAt());

        // Existing behaviour must be unchanged: a price-less product is sold out
        // and therefore not available, exactly as before these columns existed.
        $product->setProductPrices(new Collection());
        $this->assertTrue($product->isSoldOut());
        $this->assertFalse($product->isAvailable());
    }
}
