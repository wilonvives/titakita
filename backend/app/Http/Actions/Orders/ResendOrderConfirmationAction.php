<?php

namespace TitaKita\Http\Actions\Orders;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\EventSettingDomainObject;
use TitaKita\DomainObjects\Generated\OrderDomainObjectAbstract;
use TitaKita\DomainObjects\InvoiceDomainObject;
use TitaKita\DomainObjects\OrderItemDomainObject;
use TitaKita\DomainObjects\OrganizerDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Mail\Order\OrderSummary;
use TitaKita\Repository\Eloquent\Value\Relationship;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\OrderRepositoryInterface;
use TitaKita\Services\Domain\Email\MailBuilderService;
use Illuminate\Http\Response;
use Illuminate\Mail\Mailer;

class ResendOrderConfirmationAction extends BaseAction
{
    public function __construct(
        private readonly EventRepositoryInterface $eventRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly Mailer                   $mailer,
        private readonly MailBuilderService       $mailBuilderService,
    )
    {
    }

    /**
     * @todo - move this to a handler
     */
    public function __invoke(int $eventId, int $orderId): Response
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $order = $this->orderRepository
            ->loadRelation(OrderItemDomainObject::class)
            ->loadRelation(InvoiceDomainObject::class)
            ->findFirstWhere([
                OrderDomainObjectAbstract::EVENT_ID => $eventId,
                OrderDomainObjectAbstract::ID => $orderId,
            ]);

        if (!$order) {
            return $this->notFoundResponse();
        }

        if ($order->isOrderCompleted()) {
            $event = $this->eventRepository
                ->loadRelation(new Relationship(OrganizerDomainObject::class, name: 'organizer'))
                ->loadRelation(new Relationship(EventSettingDomainObject::class))
                ->findById($order->getEventId());

            $mail = $this->mailBuilderService->buildOrderSummaryMail(
                $order,
                $event,
                $event->getEventSettings(),
                $event->getOrganizer(),
                $order->getLatestInvoice()
            );

            $this->mailer
                ->to($order->getEmail())
                ->locale($order->getLocale())
                ->send($mail);
        }

        return $this->noContentResponse();
    }
}
