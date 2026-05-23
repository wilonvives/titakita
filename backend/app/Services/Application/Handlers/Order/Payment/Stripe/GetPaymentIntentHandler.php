<?php

namespace TitaKita\Services\Application\Handlers\Order\Payment\Stripe;

use TitaKita\DomainObjects\OrderDomainObject;
use TitaKita\DomainObjects\Status\OrderPaymentStatus;
use TitaKita\DomainObjects\StripePaymentDomainObject;
use TitaKita\Repository\Eloquent\Value\Relationship;
use TitaKita\Repository\Interfaces\OrderRepositoryInterface;
use TitaKita\Services\Application\Handlers\Order\Payment\Stripe\DTO\StripePaymentIntentPublicDTO;
use TitaKita\Services\Domain\Payment\Stripe\EventHandlers\PaymentIntentSucceededHandler;
use TitaKita\Services\Infrastructure\Stripe\StripeClientFactory;
use Psr\Log\LoggerInterface;
use Stripe\Exception\ApiErrorException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class GetPaymentIntentHandler
{
    public function __construct(
        private readonly StripeClientFactory           $stripeClientFactory,
        private readonly OrderRepositoryInterface      $orderRepository,
        private readonly LoggerInterface               $logger,
        private readonly PaymentIntentSucceededHandler $paymentIntentSucceededHandler,
    )
    {
    }

    public function handle(int $eventId, string $orderShortId): StripePaymentIntentPublicDTO
    {
        /** @var OrderDomainObject $order */
        $order = $this->orderRepository
            ->loadRelation(new Relationship(
                domainObject: StripePaymentDomainObject::class,
                name: 'stripe_payment',
            ))
            ->findFirstWhere([
                'event_id' => $eventId,
                'short_id' => $orderShortId
            ]);

        $accountId = $order->getStripePayment()->getConnectedAccountId();
        $paymentPlatform = $order->getStripePayment()->getStripePlatformEnum();

        try {
            $stripeClient = $this->stripeClientFactory->createForPlatform($paymentPlatform);
            $paymentIntent = $stripeClient->paymentIntents->retrieve(
                id: $order->getStripePayment()->getPaymentIntentId(),
                opts: $accountId ? ['stripe_account' => $accountId] : []
            );
        } catch (ApiErrorException $e) {
            $this->logger->error('Failed to retrieve payment intent', [
                'error' => $e->getMessage(),
                'order_id' => $order->getId(),
                'order_short_id' => $order->getShortId(),
                'payment_intent_id' => $order->getStripePayment()->getPaymentIntentId(),
            ]);

            throw new ResourceNotFoundException('Payment intent not found: ' . $e->getMessage());
        }

        // If the payment intent is a success and the order's payment status is not received, we manually handle the event here.
        // This is because the webhook may not have been received yet, or has failed for some reason.
        // This is a safety net to ensure the order is updated correctly.
        if ($paymentIntent->status === 'succeeded' && $order->getPaymentStatus() !== OrderPaymentStatus::PAYMENT_RECEIVED->name) {
            $this->paymentIntentSucceededHandler->handleEvent($paymentIntent);
        }

        return StripePaymentIntentPublicDTO::fromArray([
            'paymentIntentId' => $paymentIntent->id,
            'status' => $paymentIntent->status,
            'amount' => $paymentIntent->amount,
        ]);
    }
}
