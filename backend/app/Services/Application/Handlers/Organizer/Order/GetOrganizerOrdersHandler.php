<?php

namespace TitaKita\Services\Application\Handlers\Organizer\Order;

use TitaKita\DomainObjects\AttendeeDomainObject;
use TitaKita\DomainObjects\InvoiceDomainObject;
use TitaKita\DomainObjects\OrderItemDomainObject;
use TitaKita\Http\DTO\QueryParamsDTO;
use TitaKita\Repository\Interfaces\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetOrganizerOrdersHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
    )
    {
    }

    public function handle(int $organizer, int $accountId, QueryParamsDTO $queryParams): LengthAwarePaginator
    {
        return $this->orderRepository
            ->loadRelation(OrderItemDomainObject::class)
            ->loadRelation(AttendeeDomainObject::class)
            ->loadRelation(InvoiceDomainObject::class)
            ->findByOrganizerId(
                organizerId: $organizer,
                accountId: $accountId,
                params: $queryParams,
            );
    }
}
