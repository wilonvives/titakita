<?php

namespace TitaKita\Http\Actions\Orders;

use TitaKita\DomainObjects\AttendeeDomainObject;
use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\Generated\OrderDomainObjectAbstract;
use TitaKita\DomainObjects\OrderItemDomainObject;
use TitaKita\DomainObjects\QuestionAndAnswerViewDomainObject;
use TitaKita\Exceptions\ResourceNotFoundException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Repository\Eloquent\Value\OrderAndDirection;
use TitaKita\Repository\Eloquent\Value\Relationship;
use TitaKita\Repository\Interfaces\OrderRepositoryInterface;
use TitaKita\Resources\Order\OrderResource;
use Illuminate\Http\JsonResponse;

class GetOrderAction extends BaseAction
{
    private OrderRepositoryInterface $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @throws ResourceNotFoundException
     */
    public function __invoke(int $eventId, int $orderId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $order = $this->orderRepository
            ->loadRelation(OrderItemDomainObject::class)
            ->loadRelation(AttendeeDomainObject::class)
            ->loadRelation(new Relationship(domainObject: QuestionAndAnswerViewDomainObject::class, orderAndDirections: [
                new OrderAndDirection(order: 'question_id'),
            ]))
            ->findFirstWhere([
                OrderDomainObjectAbstract::ID => $orderId,
                OrderDomainObjectAbstract::EVENT_ID => $eventId,
            ]);

        if ($order === null) {
            throw new ResourceNotFoundException(__('Order not found'));
        }

        return $this->resourceResponse(OrderResource::class, $order);
    }
}
