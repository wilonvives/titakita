<?php

namespace TitaKita\Listeners\Waitlist;

use TitaKita\DomainObjects\Enums\CapacityChangeDirection;
use TitaKita\DomainObjects\EventSettingDomainObject;
use TitaKita\Events\CapacityChangedEvent;
use TitaKita\Exceptions\NoCapacityAvailableException;
use TitaKita\Repository\Eloquent\Value\Relationship;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Services\Domain\Product\AvailableProductQuantitiesFetchService;
use TitaKita\Services\Domain\Waitlist\ProcessWaitlistService;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessWaitlistOnCapacityAvailableListener implements ShouldQueue
{
    public function __construct(
        private readonly EventRepositoryInterface               $eventRepository,
        private readonly ProcessWaitlistService                 $processWaitlistService,
        private readonly AvailableProductQuantitiesFetchService $availableQuantitiesService,
    )
    {
    }

    public function handle(CapacityChangedEvent $event): void
    {
        if ($event->direction !== CapacityChangeDirection::INCREASED) {
            return;
        }

        $eventDomainObject = $this->eventRepository
            ->loadRelation(new Relationship(EventSettingDomainObject::class))
            ->findById($event->eventId);

        $eventSettings = $eventDomainObject->getEventSettings();

        if (!$eventSettings?->getWaitlistAutoProcess()) {
            return;
        }

        $quantities = $this->availableQuantitiesService->getAvailableProductQuantities(
            $event->eventId,
            ignoreCache: true,
        );

        foreach ($quantities->productQuantities as $productQuantity) {
            if ($event->productId !== null && $productQuantity->product_id !== $event->productId) {
                continue;
            }

            $availableCount = max(0, $productQuantity->quantity_available);

            if ($availableCount <= 0) {
                continue;
            }

            try {
                $this->processWaitlistService->offerToNext(
                    productPriceId: $productQuantity->price_id,
                    quantity: $availableCount,
                    event: $eventDomainObject,
                    eventSettings: $eventSettings,
                );
            } catch (NoCapacityAvailableException) {
                // Expected: no waiting entries or capacity consumed by pending offers
            }
        }
    }
}
