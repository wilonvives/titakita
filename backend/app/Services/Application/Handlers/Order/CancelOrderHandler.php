<?php

namespace TitaKita\Services\Application\Handlers\Order;

use TitaKita\DomainObjects\Generated\OrderDomainObjectAbstract;
use TitaKita\DomainObjects\OrderDomainObject;
use TitaKita\Exceptions\ResourceConflictException;
use TitaKita\Repository\Interfaces\OrderRepositoryInterface;
use TitaKita\Services\Application\Handlers\Order\DTO\CancelOrderDTO;
use TitaKita\Services\Application\Handlers\Order\DTO\RefundOrderDTO;
use TitaKita\Services\Application\Handlers\Order\Payment\Stripe\RefundOrderHandler;
use TitaKita\Services\Domain\Order\OrderCancelService;
use Illuminate\Database\DatabaseManager;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Throwable;

class CancelOrderHandler
{
    public function __construct(
        private readonly OrderCancelService       $orderCancelService,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly DatabaseManager          $databaseManager,
        private readonly RefundOrderHandler       $refundOrderHandler,
    )
    {
    }

    /**
     * @throws Throwable
     * @throws ResourceConflictException
     */
    public function handle(CancelOrderDTO $cancelOrderDTO): OrderDomainObject
    {
        return $this->databaseManager->transaction(function () use ($cancelOrderDTO) {
            $order = $this->orderRepository
                ->findFirstWhere([
                    OrderDomainObjectAbstract::EVENT_ID => $cancelOrderDTO->eventId,
                    OrderDomainObjectAbstract::ID => $cancelOrderDTO->orderId,
                ]);

            if (!$order) {
                throw new ResourceNotFoundException(__('Order not found'));
            }

            if ($order->isOrderCancelled()) {
                throw new ResourceConflictException(__('Order already cancelled'));
            }

            $this->orderCancelService->cancelOrder($order);

            if ($cancelOrderDTO->refund && $order->isRefundable()) {
                $refundDTO = new RefundOrderDTO(
                    event_id: $cancelOrderDTO->eventId,
                    order_id: $cancelOrderDTO->orderId,
                    amount: $order->getTotalGross() - $order->getTotalRefunded(),
                    notify_buyer: true,
                    cancel_order: false,
                );

                $this->refundOrderHandler->handle($refundDTO);
            }

            return $this->orderRepository->findById($order->getId());
        });
    }
}
