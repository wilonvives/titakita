<?php

namespace TitaKita\Http\Actions\Orders;

use TitaKita\DomainObjects\AttendeeDomainObject;
use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\InvoiceDomainObject;
use TitaKita\DomainObjects\OrderDomainObject;
use TitaKita\DomainObjects\OrderItemDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Repository\Interfaces\OrderRepositoryInterface;
use TitaKita\Resources\Order\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetOrdersAction extends BaseAction
{
    private OrderRepositoryInterface $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function __invoke(Request $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $orders = $this->orderRepository
            ->loadRelation(OrderItemDomainObject::class)
            ->loadRelation(AttendeeDomainObject::class)
            ->loadRelation(InvoiceDomainObject::class)
            ->findByEventId($eventId, $this->getPaginationQueryParams($request));

        return $this->filterableResourceResponse(
            resource: OrderResource::class,
            data: $orders,
            domainObject: OrderDomainObject::class
        );
    }
}
