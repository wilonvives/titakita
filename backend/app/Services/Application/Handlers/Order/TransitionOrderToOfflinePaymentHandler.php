<?php

namespace TitaKita\Services\Application\Handlers\Order;

use TitaKita\DomainObjects\Enums\PaymentProviders;
use TitaKita\DomainObjects\EventSettingDomainObject;
use TitaKita\DomainObjects\Generated\OrderDomainObjectAbstract;
use TitaKita\DomainObjects\OrderDomainObject;
use TitaKita\DomainObjects\OrderItemDomainObject;
use TitaKita\DomainObjects\Status\OrderPaymentStatus;
use TitaKita\DomainObjects\Status\OrderStatus;
use TitaKita\Events\OrderStatusChangedEvent;
use TitaKita\Exceptions\ResourceConflictException;
use TitaKita\Exceptions\UnauthorizedException;
use TitaKita\Repository\Interfaces\EventSettingsRepositoryInterface;
use TitaKita\Repository\Interfaces\OrderRepositoryInterface;
use TitaKita\Services\Application\Handlers\Order\DTO\TransitionOrderToOfflinePaymentPublicDTO;
use TitaKita\Services\Domain\Product\ProductQuantityUpdateService;
use TitaKita\Services\Infrastructure\DomainEvents\DomainEventDispatcherService;
use TitaKita\Services\Infrastructure\DomainEvents\Enums\DomainEventType;
use TitaKita\Services\Infrastructure\DomainEvents\Events\OrderEvent;
use TitaKita\Services\Infrastructure\Session\CheckoutSessionManagementService;
use Illuminate\Database\DatabaseManager;

class TransitionOrderToOfflinePaymentHandler
{
    public function __construct(
        private readonly ProductQuantityUpdateService     $productQuantityUpdateService,
        private readonly OrderRepositoryInterface         $orderRepository,
        private readonly DatabaseManager                  $databaseManager,
        private readonly EventSettingsRepositoryInterface $eventSettingsRepository,
        private readonly DomainEventDispatcherService     $domainEventDispatcherService,
        private readonly CheckoutSessionManagementService $sessionManagementService,
    )
    {
    }

    public function handle(TransitionOrderToOfflinePaymentPublicDTO $dto): OrderDomainObject
    {
        return $this->databaseManager->transaction(function () use ($dto) {
            /** @var OrderDomainObjectAbstract $order */
            $order = $this->orderRepository
                ->loadRelation(OrderItemDomainObject::class)
                ->findByShortId($dto->orderShortId);

            if ($order === null) {
                throw new ResourceConflictException(__('Order not found'));
            }

            if ($order->getSessionId() === null
                || !$this->sessionManagementService->verifySession($order->getSessionId())) {
                throw new UnauthorizedException(
                    __('Sorry, we could not verify your session. Please restart your order.')
                );
            }

            /** @var EventSettingDomainObject $eventSettings */
            $eventSettings = $this->eventSettingsRepository->findFirstWhere([
                'event_id' => $order->getEventId(),
            ]);

            $this->validateOfflinePayment($order, $eventSettings);

            $this->updateOrderStatuses($order->getId());

            $this->productQuantityUpdateService->updateQuantitiesFromOrder($order);

            $order = $this->orderRepository
                ->loadRelation(OrderItemDomainObject::class)
                ->findById($order->getId());

            event(new OrderStatusChangedEvent(
                order: $order,
                sendEmails: true,
                createInvoice: $eventSettings->getEnableInvoicing(),
            ));

            $this->domainEventDispatcherService->dispatch(
                new OrderEvent(
                    type: DomainEventType::ORDER_CREATED,
                    orderId: $order->getId(),
                ),
            );

            return $order;
        });
    }

    private function updateOrderStatuses(int $orderId): void
    {
        $this->orderRepository
            ->updateFromArray($orderId, [
                OrderDomainObjectAbstract::PAYMENT_STATUS => OrderPaymentStatus::AWAITING_OFFLINE_PAYMENT->name,
                OrderDomainObjectAbstract::STATUS => OrderStatus::AWAITING_OFFLINE_PAYMENT->name,
                OrderDomainObjectAbstract::PAYMENT_PROVIDER => PaymentProviders::OFFLINE->value,
            ]);
    }

    /**
     * @throws ResourceConflictException
     */
    public function validateOfflinePayment(
        OrderDomainObject        $order,
        EventSettingDomainObject $settings,
    ): void
    {
        if (!$order->isOrderReserved()) {
            throw new ResourceConflictException(__('Order is not in the correct status to transition to offline payment'));
        }

        if ($order->isReservedOrderExpired()) {
            throw new ResourceConflictException(__('Order reservation has expired'));
        }

        if (collect($settings->getPaymentProviders())->contains(PaymentProviders::OFFLINE->value) === false) {
            throw new UnauthorizedException(__('Offline payments are not enabled for this event'));
        }
    }
}
