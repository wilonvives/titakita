<?php

namespace TitaKita\Services\Infrastructure\DomainEvents;

use TitaKita\Services\Infrastructure\DomainEvents\Events\BaseDomainEvent;
use Illuminate\Events\Dispatcher as EventDispatcher;
use Psr\Log\LoggerInterface;
use Throwable;

class DomainEventDispatcherService
{
    public function __construct(
        private readonly EventDispatcher $dispatcher,
        private readonly LoggerInterface $logger,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function dispatch(BaseDomainEvent $event): void
    {
        try {
            $this->dispatcher->dispatch($event);
        } catch (Throwable $e) {
            $this->logger->error('Failed to dispatch domain event', ['event' => $event, 'exception' => $e]);

            throw $e;
        }
    }
}
