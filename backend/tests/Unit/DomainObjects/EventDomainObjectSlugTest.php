<?php

namespace Tests\Unit\DomainObjects;

use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\OrganizerDomainObject;
use Tests\TestCase;

class EventDomainObjectSlugTest extends TestCase
{
    public function test_event_slug_from_latin_title(): void
    {
        $event = new EventDomainObject();
        $event->setId(5);
        $event->setTitle('Summer Festival');

        $this->assertSame('summer-festival', $event->getSlug());
    }

    public function test_event_slug_falls_back_for_non_latin_title(): void
    {
        $event = new EventDomainObject();
        $event->setId(7);
        $event->setTitle('的首次活动');

        $this->assertSame('event-7', $event->getSlug());
    }

    public function test_organizer_slug_falls_back_for_non_latin_name(): void
    {
        $organizer = new OrganizerDomainObject();
        $organizer->setId(3);
        $organizer->setName('音乐工作室');

        $this->assertSame('organizer-3', $organizer->getSlug());
    }
}
