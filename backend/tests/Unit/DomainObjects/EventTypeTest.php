<?php

namespace Tests\Unit\DomainObjects;

use TitaKita\DomainObjects\Enums\EventType;
use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EventTypeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_event_type_enum_has_event_and_booking(): void
    {
        $this->assertSame('event', EventType::EVENT->value);
        $this->assertSame('booking', EventType::BOOKING->value);
    }

    public function test_fresh_event_domain_object_defaults_to_event(): void
    {
        $event = new EventDomainObject();

        $this->assertSame(EventType::EVENT->value, $event->getEventType());
    }

    public function test_existing_event_has_event_type_of_event(): void
    {
        /** @var EventRepositoryInterface $repository */
        $repository = app(EventRepositoryInterface::class);

        $event = $repository->findFirstWhere([]);

        $this->assertNotNull($event, 'Expected at least one seeded event to exist.');
        $this->assertSame(EventType::EVENT->value, $event->getEventType());
    }
}
