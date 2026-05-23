<?php

namespace TitaKita\Jobs\Event\Webhook;

use TitaKita\Services\Domain\TitaKita\DirectoryReportingService;
use TitaKita\Services\Infrastructure\DomainEvents\Enums\DomainEventType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Reports an event lifecycle change to the configured TitaKita directory.
 * No-op when no directory is configured (handled inside the service).
 */
class ReportEventToDirectoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int             $eventId,
        public DomainEventType $eventType,
    )
    {
    }

    public function handle(DirectoryReportingService $directoryReportingService): void
    {
        $directoryReportingService->report($this->eventId, $this->eventType);
    }
}
