<?php

namespace TitaKita\Listeners\Waitlist;

use Carbon\Carbon;
use TitaKita\DomainObjects\Status\OrderStatus;
use TitaKita\DomainObjects\Status\WaitlistEntryStatus;
use TitaKita\DomainObjects\WaitlistEntryDomainObject;
use TitaKita\Events\OrderStatusChangedEvent;
use TitaKita\DomainObjects\Enums\CapacityChangeDirection;
use TitaKita\Events\CapacityChangedEvent;
use TitaKita\Repository\Interfaces\ProductPriceRepositoryInterface;
use TitaKita\Repository\Interfaces\WaitlistEntryRepositoryInterface;
use Illuminate\Database\DatabaseManager;

class ResolveWaitlistEntryOnOrderCompletedListener
{
    public function __construct(
        private readonly WaitlistEntryRepositoryInterface $waitlistEntryRepository,
        private readonly ProductPriceRepositoryInterface  $productPriceRepository,
        private readonly DatabaseManager                  $databaseManager,
    )
    {
    }

    public function handle(OrderStatusChangedEvent $event): void
    {
        $order = $event->order;

        if ($order->getStatus() === OrderStatus::COMPLETED->name) {
            $this->resolveByOrderId($order->getId());
            return;
        }

        if ($order->getStatus() === OrderStatus::CANCELLED->name) {
            $this->revertOfferedEntriesByOrderId($order->getId());
        }
    }

    private function resolveByOrderId(int $orderId): void
    {
        $this->databaseManager->transaction(function () use ($orderId) {
            $entries = $this->waitlistEntryRepository->findWhere([
                'order_id' => $orderId,
                ['status', 'in', [WaitlistEntryStatus::OFFERED->name]],
            ]);

            foreach ($entries as $entry) {
                $this->markAsPurchased($entry);
            }
        });
    }

    private function revertOfferedEntriesByOrderId(int $orderId): void
    {
        $capacityEvents = [];

        $this->databaseManager->transaction(function () use ($orderId, &$capacityEvents) {
            $entries = $this->waitlistEntryRepository->findWhere([
                'order_id' => $orderId,
                ['status', 'in', [WaitlistEntryStatus::OFFERED->name]],
            ]);

            foreach ($entries as $entry) {
                $this->revertToWaiting($entry);

                $productPrice = $this->productPriceRepository->findById($entry->getProductPriceId());
                $capacityEvents[] = new CapacityChangedEvent(
                    eventId: $entry->getEventId(),
                    direction: CapacityChangeDirection::INCREASED,
                    productId: $productPrice->getProductId(),
                    productPriceId: $entry->getProductPriceId(),
                );
            }
        });

        foreach ($capacityEvents as $capacityEvent) {
            event($capacityEvent);
        }
    }

    private function markAsPurchased(WaitlistEntryDomainObject $entry): void
    {
        $this->waitlistEntryRepository->updateWhere(
            attributes: [
                'status' => WaitlistEntryStatus::PURCHASED->name,
                'purchased_at' => Carbon::now()->toDateTimeString(),
            ],
            where: [
                'id' => $entry->getId(),
                'status' => WaitlistEntryStatus::OFFERED->name,
            ],
        );
    }

    private function revertToWaiting(WaitlistEntryDomainObject $entry): void
    {
        $this->waitlistEntryRepository->updateWhere(
            attributes: [
                'status' => WaitlistEntryStatus::WAITING->name,
                'order_id' => null,
                'offered_at' => null,
                'offer_expires_at' => null,
                'offer_token' => null,
            ],
            where: [
                'id' => $entry->getId(),
                'status' => WaitlistEntryStatus::OFFERED->name,
            ],
        );
    }
}
