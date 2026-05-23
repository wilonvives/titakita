<?php

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\OrderItemDomainObject;
use TitaKita\Models\OrderItem;
use TitaKita\Repository\Interfaces\OrderItemRepositoryInterface;

/**
 * @extends BaseRepository<OrderItemDomainObject>
 */
class OrderItemRepository extends BaseRepository implements OrderItemRepositoryInterface
{
    protected function getModel(): string
    {
        return OrderItem::class;
    }

    public function getDomainObject(): string
    {
        return OrderItemDomainObject::class;
    }
}
