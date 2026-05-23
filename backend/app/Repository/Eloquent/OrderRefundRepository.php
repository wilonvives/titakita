<?php

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\OrderRefundDomainObject;
use TitaKita\Models\OrderRefund;
use TitaKita\Repository\Interfaces\OrderRefundRepositoryInterface;

/**
 * @extends BaseRepository<OrderRefundDomainObject>
 */
class OrderRefundRepository extends BaseRepository implements OrderRefundRepositoryInterface
{
    protected function getModel(): string
    {
        return OrderRefund::class;
    }

    public function getDomainObject(): string
    {
        return OrderRefundDomainObject::class;
    }
}
