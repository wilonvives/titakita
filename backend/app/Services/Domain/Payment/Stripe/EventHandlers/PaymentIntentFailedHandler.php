<?php

namespace TitaKita\Services\Domain\Payment\Stripe\EventHandlers;

use TitaKita\DomainObjects\Generated\OrderDomainObjectAbstract;
use TitaKita\DomainObjects\Generated\StripePaymentDomainObjectAbstract;
use TitaKita\DomainObjects\OrderDomainObject;
use TitaKita\DomainObjects\OrderItemDomainObject;
use TitaKita\DomainObjects\Status\OrderPaymentStatus;
use TitaKita\Events\OrderStatusChangedEvent;
use TitaKita\Repository\Eloquent\StripePaymentsRepository;
use TitaKita\Repository\Eloquent\Value\Relationship;
use TitaKita\Repository\Interfaces\OrderRepositoryInterface;
use TitaKita\Services\Domain\Payment\Stripe\StripePaymentUpdateFromPaymentIntentService;
use Illuminate\Database\DatabaseManager;
use Stripe\PaymentIntent;
use Throwable;

readonly class PaymentIntentFailedHandler
{
    public function __construct(
        private OrderRepositoryInterface                    $orderRepository,
        private StripePaymentsRepository                    $stripePaymentsRepository,
        private DatabaseManager                             $databaseManager,
        private StripePaymentUpdateFromPaymentIntentService $stripePaymentUpdateFromPaymentIntentService,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function handleEvent(PaymentIntent $paymentIntent): void
    {
        $this->databaseManager->transaction(function () use ($paymentIntent) {
            /** @var StripePaymentDomainObjectAbstract $stripePayment */
            $stripePayment = $this->stripePaymentsRepository
                ->loadRelation(new Relationship(OrderDomainObject::class, name: 'order'))
                ->findFirstWhere([
                    StripePaymentDomainObjectAbstract::PAYMENT_INTENT_ID => $paymentIntent->id,
                ]);

            $this->stripePaymentUpdateFromPaymentIntentService->updateStripePaymentInfo($paymentIntent, $stripePayment);

            $updatedOrder = $this->updateOrderStatuses($stripePayment);

            OrderStatusChangedEvent::dispatch($updatedOrder);
        });
    }

    private function updateOrderStatuses(StripePaymentDomainObjectAbstract $stripePayment): OrderDomainObject
    {
        return $this->orderRepository
            ->loadRelation(OrderItemDomainObject::class)
            ->updateFromArray($stripePayment->getOrderId(), [
                OrderDomainObjectAbstract::PAYMENT_STATUS => OrderPaymentStatus::PAYMENT_FAILED->name,
            ]);
    }
}
