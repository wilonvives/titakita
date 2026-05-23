<?php

namespace TitaKita\Services\Domain\Order;

use Brick\Math\Exception\MathException;
use TitaKita\DomainObjects\AccountConfigurationDomainObject;
use TitaKita\DomainObjects\AccountDomainObject;
use TitaKita\DomainObjects\AttendeeDomainObject;
use TitaKita\DomainObjects\Enums\PaymentProviders;
use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\EventSettingDomainObject;
use TitaKita\DomainObjects\Generated\OrderDomainObjectAbstract;
use TitaKita\DomainObjects\InvoiceDomainObject;
use TitaKita\DomainObjects\OrderDomainObject;
use TitaKita\DomainObjects\OrderItemDomainObject;
use TitaKita\DomainObjects\OrganizerDomainObject;
use TitaKita\DomainObjects\Status\AttendeeStatus;
use TitaKita\DomainObjects\Status\InvoiceStatus;
use TitaKita\DomainObjects\Status\OrderApplicationFeeStatus;
use TitaKita\DomainObjects\Status\OrderPaymentStatus;
use TitaKita\DomainObjects\Status\OrderStatus;
use TitaKita\Events\OrderStatusChangedEvent;
use TitaKita\Exceptions\ResourceConflictException;
use TitaKita\Repository\Eloquent\Value\Relationship;
use TitaKita\Repository\Interfaces\AffiliateRepositoryInterface;
use TitaKita\Repository\Interfaces\AttendeeRepositoryInterface;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\InvoiceRepositoryInterface;
use TitaKita\Repository\Interfaces\OrderRepositoryInterface;
use TitaKita\Services\Domain\Mail\SendOrderDetailsService;
use TitaKita\Services\Infrastructure\DomainEvents\DomainEventDispatcherService;
use TitaKita\Services\Infrastructure\DomainEvents\Enums\DomainEventType;
use TitaKita\Services\Infrastructure\DomainEvents\Events\OrderEvent;
use Illuminate\Database\DatabaseManager;
use Throwable;

class MarkOrderAsPaidService
{
    public function __construct(
        private readonly OrderRepositoryInterface              $orderRepository,
        private readonly DatabaseManager                       $databaseManager,
        private readonly AffiliateRepositoryInterface          $affiliateRepository,
        private readonly InvoiceRepositoryInterface            $invoiceRepository,
        private readonly AttendeeRepositoryInterface           $attendeeRepository,
        private readonly DomainEventDispatcherService          $domainEventDispatcherService,
        private readonly OrderApplicationFeeCalculationService $orderApplicationFeeCalculationService,
        private readonly EventRepositoryInterface              $eventRepository,
        private readonly OrderApplicationFeeService            $orderApplicationFeeService,
        private readonly SendOrderDetailsService               $sendOrderDetailsService,
    )
    {
    }

    /**
     * @throws ResourceConflictException|Throwable
     */
    public function markOrderAsPaid(
        int $orderId,
        int $eventId,
    ): OrderDomainObject
    {
        return $this->databaseManager->transaction(function () use ($orderId, $eventId) {
            /** @var OrderDomainObject $order */
            $order = $this->orderRepository
                ->loadRelation(OrderItemDomainObject::class)
                ->loadRelation(AttendeeDomainObject::class)
                ->loadRelation(InvoiceDomainObject::class)
                ->findFirstWhere([
                    OrderDomainObjectAbstract::ID => $orderId,
                    OrderDomainObjectAbstract::EVENT_ID => $eventId,
                ]);

            $event = $this->eventRepository
                ->loadRelation(new Relationship(OrganizerDomainObject::class, name: 'organizer'))
                ->loadRelation(new Relationship(EventSettingDomainObject::class))
                ->findById($order->getEventId());

            if ($order->getStatus() !== OrderStatus::AWAITING_OFFLINE_PAYMENT->name) {
                throw new ResourceConflictException(__('Order is not awaiting offline payment'));
            }

            $this->updateOrderStatus($orderId);

            $this->updateOrderInvoice($orderId);

            $updatedOrder = $this->orderRepository
                ->loadRelation(OrderItemDomainObject::class)
                ->findById($orderId);

            // Update affiliate sales if this order has an affiliate
            if ($updatedOrder->getAffiliateId()) {
                $this->affiliateRepository->incrementSales(
                    $updatedOrder->getAffiliateId(),
                    $updatedOrder->getTotalGross()
                );
            }

            $this->updateAttendeeStatuses($updatedOrder);

            event(new OrderStatusChangedEvent(
                order: $updatedOrder,
                sendEmails: false
            ));

            $this->domainEventDispatcherService->dispatch(
                new OrderEvent(
                    type: DomainEventType::ORDER_MARKED_AS_PAID,
                    orderId: $orderId,
                ),
            );

            $this->storeApplicationFeePayment($updatedOrder);

            $this->sendOrderDetailsService->sendCustomerOrderSummary(
                order: $updatedOrder,
                event: $event,
                organizer: $event->getOrganizer(),
                eventSettings: $event->getEventSettings(),
                invoice: $order->getLatestInvoice(),
            );

            return $updatedOrder;
        });
    }

    private function updateOrderInvoice(int $orderId): void
    {
        $invoice = $this->invoiceRepository->findLatestInvoiceForOrder($orderId);

        if ($invoice) {
            $this->invoiceRepository->updateFromArray($invoice->getId(), [
                'status' => InvoiceStatus::PAID->name,
            ]);
        }
    }

    private function updateOrderStatus(int $orderId): void
    {
        $this->orderRepository->updateFromArray($orderId, [
            OrderDomainObjectAbstract::STATUS => OrderStatus::COMPLETED->name,
            OrderDomainObjectAbstract::PAYMENT_STATUS => OrderPaymentStatus::PAYMENT_RECEIVED->name,
        ]);
    }

    private function updateAttendeeStatuses(OrderDomainObject $updatedOrder): void
    {
        $this->attendeeRepository->updateWhere(
            attributes: [
                'status' => AttendeeStatus::ACTIVE->name,
            ],
            where: [
                'order_id' => $updatedOrder->getId(),
                'status' => AttendeeStatus::AWAITING_PAYMENT->name,
            ],
        );
    }

    /**
     * @throws MathException
     */
    private function storeApplicationFeePayment(OrderDomainObject $updatedOrder): void
    {
        /** @var EventDomainObject $event */
        $event = $this->eventRepository
            ->loadRelation(new Relationship(
                domainObject: AccountDomainObject::class,
                nested: [
                    new Relationship(
                        domainObject: AccountConfigurationDomainObject::class,
                        name: 'configuration',
                    ),
                ],
                name: 'account'
            ))
            ->findById($updatedOrder->getEventId());

        /** @var AccountConfigurationDomainObject $config */
        $config = $event->getAccount()->getConfiguration();

        $this->orderApplicationFeeService->createOrderApplicationFee(
            orderId: $updatedOrder->getId(),
            applicationFeeAmountMinorUnit: $this->orderApplicationFeeCalculationService->calculateApplicationFee(
                accountConfiguration: $config,
                order: $updatedOrder,
            )?->netApplicationFee?->toMinorUnit() ?? 0,
            orderApplicationFeeStatus: OrderApplicationFeeStatus::AWAITING_PAYMENT,
            paymentMethod: PaymentProviders::OFFLINE,
            currency: $updatedOrder->getCurrency(),
        );
    }
}
