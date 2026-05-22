<?php

namespace HiEvents\Services\Domain\TitaKita;

use HiEvents\Repository\Interfaces\EventRepositoryInterface;
use HiEvents\Services\Infrastructure\DomainEvents\Enums\DomainEventType;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Facades\Http;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Pushes event lifecycle changes (published / updated / cancelled) to a TitaKita
 * directory, if one is configured via TITAKITA_DIRECTORY_URL. This is the third
 * leg of the node<->directory contract (discover, fetch, update).
 *
 * Reporting is strictly opt-in and best-effort: with no directory_url it is a
 * no-op, and any transport failure is logged rather than thrown so it never
 * affects the operator's own event flow.
 */
class DirectoryReportingService
{
    public function __construct(
        private readonly ConfigRepository         $config,
        private readonly EventRepositoryInterface $eventRepository,
        private readonly LoggerInterface          $logger,
    )
    {
    }

    public function report(int $eventId, DomainEventType $eventType): void
    {
        $directoryUrl = $this->config->get('titakita.directory_url');

        if (empty($directoryUrl)) {
            return;
        }

        try {
            $event = $this->eventRepository->findById($eventId);
        } catch (Throwable $e) {
            $this->logger->warning('TitaKita directory report: event not found', [
                'event_id' => $eventId,
                'error' => $e->getMessage(),
            ]);
            return;
        }

        $publicUrl = rtrim((string)$this->config->get('titakita.public_url'), '/');
        $eventUrl = $publicUrl . sprintf('/event/%d/%s', $event->getId(), $event->getSlug() ?: 'event');
        $nodeId = $this->config->get('titakita.node_id');

        $payload = [
            'node' => [
                'id' => $nodeId,
                'name' => $this->config->get('titakita.name'),
                'url' => $publicUrl,
            ],
            'type' => $eventType->value,
            'occurred_at' => now()->toIso8601String(),
            'event' => [
                '@type' => 'Event',
                'identifier' => (string)$event->getId(),
                'name' => $event->getTitle(),
                'url' => $eventUrl,
                'startDate' => $event->getStartDate(),
                'endDate' => $event->getEndDate(),
                'category' => $event->getCategory(),
                'status' => $event->getStatus(),
            ],
        ];

        try {
            Http::timeout(10)
                ->withHeaders([
                    'X-Webhook-Source' => 'TitaKita',
                    'X-TitaKita-Node' => (string)$nodeId,
                ])
                ->post($directoryUrl, $payload)
                ->throw();
        } catch (Throwable $e) {
            $this->logger->warning('TitaKita directory report failed', [
                'directory_url' => $directoryUrl,
                'event_id' => $eventId,
                'type' => $eventType->value,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
