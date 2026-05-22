<?php

namespace Tests\Unit\Resources\TitaKita;

use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\OrganizerDomainObject;
use HiEvents\Resources\TitaKita\TitaKitaEventResource;
use Illuminate\Http\Request;
use Tests\TestCase;

class TitaKitaEventResourceTest extends TestCase
{
    private function makeEvent(): EventDomainObject
    {
        // slug is derived from the title (Str::slug) — no setter.
        $event = new EventDomainObject();
        $event->setId(42);
        $event->setTitle('Summer Festival');
        $event->setCategory('music');
        $event->setDescription('<p>Great <b>fun</b></p>');
        $event->setStartDate('2026-07-01 18:00:00');
        $event->setEndDate('2026-07-01 23:00:00');
        $event->setTimezone('Asia/Taipei');
        $event->setCurrency('TWD');
        $event->setLocationDetails(['venue_name' => 'City Park']);

        return $event;
    }

    public function test_maps_event_to_schema_org_with_backlink(): void
    {
        config()->set('titakita.public_url', 'https://node.example.com');

        $data = (new TitaKitaEventResource($this->makeEvent()))->toArray(new Request());

        $this->assertSame('Event', $data['@type']);
        $this->assertSame('42', $data['identifier']);
        $this->assertSame('Summer Festival', $data['name']);
        $this->assertSame('https://node.example.com/event/42/summer-festival', $data['url']);
        $this->assertSame('music', $data['category']);
        $this->assertSame('Asia/Taipei', $data['timezone']);
        $this->assertSame('https://schema.org/EventScheduled', $data['eventStatus']);
        $this->assertSame(['venue_name' => 'City Park'], $data['location']);
        $this->assertSame('TWD', $data['offers']['priceCurrency']);
        $this->assertSame('https://node.example.com/event/42/summer-festival', $data['offers']['url']);

        // Description is plain text (no HTML) derived from the rich description.
        $this->assertArrayHasKey('description', $data);
        $this->assertStringNotContainsString('<', $data['description']);
        $this->assertStringContainsString('Great', $data['description']);
    }

    public function test_includes_organizer_with_backlink_when_loaded(): void
    {
        config()->set('titakita.public_url', 'https://node.example.com');

        // organizer slug is derived from the name (Str::slug).
        $organizer = new OrganizerDomainObject();
        $organizer->setId(7);
        $organizer->setName('Cool Org');

        $event = $this->makeEvent();
        $event->setOrganizer($organizer);

        $data = (new TitaKitaEventResource($event))->toArray(new Request());

        $this->assertSame('Organization', $data['organizer']['@type']);
        $this->assertSame('Cool Org', $data['organizer']['name']);
        $this->assertSame('https://node.example.com/events/7/cool-org', $data['organizer']['url']);
    }

    public function test_public_url_trailing_slash_is_normalised_in_backlink(): void
    {
        config()->set('titakita.public_url', 'https://node.example.com/');

        $data = (new TitaKitaEventResource($this->makeEvent()))->toArray(new Request());

        $this->assertSame('https://node.example.com/event/42/summer-festival', $data['url']);
    }
}
