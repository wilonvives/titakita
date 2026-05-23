<?php

namespace Tests\Unit\Services\Domain\TitaKita;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Services\Domain\TitaKita\DirectoryReportingService;
use TitaKita\Services\Infrastructure\DomainEvents\Enums\DomainEventType;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Mockery;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

class DirectoryReportingServiceTest extends TestCase
{
    private function makeService(EventRepositoryInterface $eventRepository): DirectoryReportingService
    {
        $logger = Mockery::mock(LoggerInterface::class)->shouldIgnoreMissing();

        return new DirectoryReportingService(app('config'), $eventRepository, $logger);
    }

    public function test_does_nothing_when_no_directory_url_configured(): void
    {
        Http::fake();
        config()->set('titakita.directory_url', null);

        $eventRepository = Mockery::mock(EventRepositoryInterface::class);
        $eventRepository->shouldNotReceive('findById');

        $this->makeService($eventRepository)->report(1, DomainEventType::EVENT_CREATED);

        Http::assertNothingSent();
    }

    public function test_posts_event_to_directory_when_configured(): void
    {
        Http::fake();
        config()->set('titakita.directory_url', 'https://directory.example.com/ingest');
        config()->set('titakita.node_id', 'node-123');
        config()->set('titakita.public_url', 'https://node.example.com');
        config()->set('titakita.name', 'My Node');

        $event = new EventDomainObject();
        $event->setId(42);
        $event->setTitle('Summer Festival');
        $event->setStartDate('2026-07-01 18:00:00');
        $event->setEndDate('2026-07-01 23:00:00');
        $event->setCategory('music');
        $event->setStatus('LIVE');

        $eventRepository = Mockery::mock(EventRepositoryInterface::class);
        $eventRepository->shouldReceive('findById')->once()->with(42)->andReturn($event);

        $this->makeService($eventRepository)->report(42, DomainEventType::EVENT_ARCHIVED);

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://directory.example.com/ingest'
                && $request['type'] === 'event.archived'
                && $request['node']['id'] === 'node-123'
                && $request['node']['url'] === 'https://node.example.com'
                && $request['event']['identifier'] === '42'
                && $request['event']['name'] === 'Summer Festival'
                && $request['event']['url'] === 'https://node.example.com/event/42/summer-festival'
                && $request->hasHeader('X-Webhook-Source', 'TitaKita')
                && $request->hasHeader('X-TitaKita-Node', 'node-123');
        });
    }
}
