<?php

namespace TitaKita\Services\Domain\Order;

use TitaKita\DomainObjects\AttendeeDomainObject;
use TitaKita\DomainObjects\EventSettingDomainObject;
use TitaKita\DomainObjects\OrderDomainObject;
use TitaKita\DomainObjects\OrganizerDomainObject;
use TitaKita\DomainObjects\Status\AttendeeStatus;
use TitaKita\DomainObjects\Status\OrderStatus;
use TitaKita\DomainObjects\Enums\CapacityChangeDirection;
use TitaKita\Events\CapacityChangedEvent;
use TitaKita\Mail\Order\OrderCancelled;
use TitaKita\Repository\Eloquent\Value\Relationship;
use TitaKita\Repository\Interfaces\AttendeeRepositoryInterface;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\OrderRepositoryInterface;
use TitaKita\Services\Domain\Product\ProductQuantityUpdateService;
use TitaKita\Services\Infrastructure\DomainEvents\DomainEventDispatcherService;
use TitaKita\Services\Infrastructure\DomainEvents\Enums\DomainEventType;
use TitaKita\Services\Infrastructure\DomainEvents\Events\OrderEvent;
use TitaKita\Services\Domain\EventStatistics\EventStatisticsCancellationService;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Database\DatabaseManager;
use Throwable;

class OrderCancelService
{
    public function __construct(
        private readonly Mailer                              $mailer,
        private readonly AttendeeRepositoryInterface         $attendeeRepository,
        private readonly EventRepositoryInterface            $eventRepository,
        private readonly OrderRepositoryInterface            $orderRepository,
        private readonly DatabaseManager                     $databaseManager,
        private readonly ProductQuantityUpdateService        $productQuantityService,
        private readonly DomainEventDispatcherService        $domainEventDispatcherService,
        private readonly EventStatisticsCancellationService  $eventStatisticsCancellationService,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function cancelOrder(OrderDomainObject $order): void
    {
        $this->databaseManager->transaction(function () use ($order) {
            // Order of operations matters here. We must decrement the stats first.
            $this->eventStatisticsCancellationService->decrementForCancelledOrder($order);

            $this->adjustProductQuantities($order);
            $this->cancelAttendees($order);
            $this->updateOrderStatus($order);

            $event = $this->eventRepository
                ->loadRelation(new Relationship(OrganizerDomainObject::class, name: 'organizer'))
                ->loadRelation(EventSettingDomainObject::class)
                ->findById($order->getEventId());

            $this->mailer
                ->to($order->getEmail())
                ->locale($order->getLocale())
                ->send(new OrderCancelled(
                    order: $order,
                    event: $event,
                    organizer: $event->getOrganizer(),
                    eventSettings: $event->getEventSettings(),
                ));

            $this->domainEventDispatcherService->dispatch(
                new OrderEvent(
                    type: DomainEventType::ORDER_CANCELLED,
                    orderId: $order->getId(),
                ),
            );

            $this->dispatchCapacityChangedEvents($order);
        });
    }

    private function cancelAttendees(OrderDomainObject $order): void
    {
        $this->attendeeRepository->updateWhere(
            attributes: [
                'status' => AttendeeStatus::CANCELLED->name,
            ],
            where: [
                'order_id' => $order->getId(),
            ]
        );
    }

    private function adjustProductQuantities(OrderDomainObject $order): void
    {
        $attendees = $this->attendeeRepository->findWhere([
            'order_id' => $order->getId(),
        ])->filter(function (AttendeeDomainObject $attendee) use ($order) {
            if ($order->isOrderAwaitingOfflinePayment()) {
                return $attendee->getStatus() === AttendeeStatus::ACTIVE->name
                    || $attendee->getStatus() === AttendeeStatus::AWAITING_PAYMENT->name;
            }

            return $attendee->getStatus() === AttendeeStatus::ACTIVE->name;
        });

        $productIdCountMap = $attendees
            ->map(fn(AttendeeDomainObject $attendee) => $attendee->getProductPriceId())->countBy();

        foreach ($productIdCountMap as $productPriceId => $count) {
            $this->productQuantityService->decreaseQuantitySold($productPriceId, $count);
        }
    }

    private function updateOrderStatus(OrderDomainObject $order): void
    {
        $this->orderRepository->updateWhere(
            attributes: [
                'status' => OrderStatus::CANCELLED->name,
            ],
            where: [
                'id' => $order->getId(),
            ]
        );
    }

    private function dispatchCapacityChangedEvents(OrderDomainObject $order): void
    {
        $attendees = $this->attendeeRepository->findWhere([
            'order_id' => $order->getId(),
        ]);

        $productIds = $attendees
            ->map(fn(AttendeeDomainObject $attendee) => $attendee->getProductId())
            ->unique();

        foreach ($productIds as $productId) {
            event(new CapacityChangedEvent(
                eventId: $order->getEventId(),
                direction: CapacityChangeDirection::INCREASED,
                productId: $productId,
            ));
        }
    }
}
