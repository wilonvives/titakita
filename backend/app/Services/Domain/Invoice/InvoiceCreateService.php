<?php

namespace TitaKita\Services\Domain\Invoice;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\EventSettingDomainObject;
use TitaKita\DomainObjects\InvoiceDomainObject;
use TitaKita\DomainObjects\OrderItemDomainObject;
use TitaKita\DomainObjects\Status\InvoiceStatus;
use TitaKita\Exceptions\ResourceConflictException;
use TitaKita\Repository\Eloquent\Value\Relationship;
use TitaKita\Repository\Interfaces\InvoiceRepositoryInterface;
use TitaKita\Repository\Interfaces\OrderRepositoryInterface;

class InvoiceCreateService
{
    public function __construct(
        private readonly OrderRepositoryInterface   $orderRepository,
        private readonly InvoiceRepositoryInterface $invoiceRepository,
    )
    {
    }

    /**
     * @throws ResourceConflictException
     */
    public function createInvoiceForOrder(int $orderId): InvoiceDomainObject
    {
        $existingInvoice = $this->invoiceRepository->findFirstWhere([
            'order_id' => $orderId,
        ]);

        if ($existingInvoice) {
            throw new ResourceConflictException(__('Invoice already exists'));
        }

        $order = $this->orderRepository
            ->loadRelation(OrderItemDomainObject::class)
            ->loadRelation(new Relationship(EventDomainObject::class, nested: [
                new Relationship(EventSettingDomainObject::class, name: 'event_settings'),
            ], name: 'event'))
            ->findById($orderId);

        /** @var EventSettingDomainObject $eventSettings */
        $eventSettings = $order->getEvent()->getEventSettings();
        /** @var EventDomainObject $event */
        $event = $order->getEvent();

        return $this->invoiceRepository->create([
            'order_id' => $orderId,
            'account_id' => $event->getAccountId(),
            'invoice_number' => $this->getLatestInvoiceNumber($event->getId(), $eventSettings),
            'items' => collect($order->getOrderItems())->map(fn(OrderItemDomainObject $item) => $item->toArray())->toArray(),
            'taxes_and_fees' => $order->getTaxesAndFeesRollup(),
            'issue_date' => now()->toDateString(),
            'status' => $order->isOrderCompleted() ? InvoiceStatus::PAID->name : InvoiceStatus::UNPAID->name,
            'total_amount' => $order->getTotalGross(),
            'due_date' => $eventSettings->getInvoicePaymentTermsDays() !== null
                ? now()->addDays($eventSettings->getInvoicePaymentTermsDays())
                : null
        ]);
    }

    private function getLatestInvoiceNumber(int $eventId, EventSettingDomainObject $eventSettings): string
    {
        $latestInvoice = $this->invoiceRepository->findLatestInvoiceForEvent($eventId);

        $startNumber = $eventSettings->getInvoiceStartNumber() ?? 1;
        $prefix = $eventSettings->getInvoicePrefix() ?? '';

        if (!$latestInvoice) {
            return $prefix . $startNumber;
        }

        $nextInvoiceNumber = (int)preg_replace('/\D+/', '', $latestInvoice->getInvoiceNumber()) + 1;

        return $prefix . $nextInvoiceNumber;
    }
}
