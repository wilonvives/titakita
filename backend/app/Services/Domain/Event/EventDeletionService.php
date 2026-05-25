<?php

namespace TitaKita\Services\Domain\Event;

use TitaKita\DomainObjects\Status\OrderStatus;
use TitaKita\Exceptions\CannotDeleteEntityException;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\OrderRepositoryInterface;
use Illuminate\Database\DatabaseManager;
use Psr\Log\LoggerInterface;
use Throwable;

class EventDeletionService
{
    public function __construct(
        private readonly EventRepositoryInterface $eventRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly LoggerInterface          $logger,
        private readonly DatabaseManager          $databaseManager,
    )
    {
    }

    public function canDeleteEvent(int $eventId): bool
    {
        $completedOrders = $this->orderRepository->findWhere([
            'event_id' => $eventId,
            'status' => OrderStatus::COMPLETED->name,
        ]);

        foreach ($completedOrders as $order) {
            if ($order->getTotalGross() > $order->getTotalRefunded()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws CannotDeleteEntityException
     * @throws Throwable
     */
    public function deleteEvent(int $eventId, int $accountId): void
    {
        $this->databaseManager->transaction(function () use ($eventId, $accountId) {
            if (!$this->canDeleteEvent($eventId)) {
                throw new CannotDeleteEntityException(
                    __('This event has paid attendees with payments that have not been refunded. Please refund each attendee from the Orders page before deleting.')
                );
            }

            $this->eventRepository->deleteWhere([
                'id' => $eventId,
                'account_id' => $accountId,
            ]);
        });

        $this->logger->info('Event deleted', [
            'event_id' => $eventId,
            'account_id' => $accountId,
        ]);
    }
}
