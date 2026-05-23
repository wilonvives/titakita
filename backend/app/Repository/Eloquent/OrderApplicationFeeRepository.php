<?php

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\OrderApplicationFeeDomainObject;
use TitaKita\Models\OrderApplicationFee;
use TitaKita\Repository\Interfaces\OrderApplicationFeeRepositoryInterface;

/**
 * @extends BaseRepository<OrderApplicationFeeDomainObject>
 */
class OrderApplicationFeeRepository extends BaseRepository implements OrderApplicationFeeRepositoryInterface
{
    protected function getModel(): string
    {
        return OrderApplicationFee::class;
    }

    public function getDomainObject(): string
    {
        return OrderApplicationFeeDomainObject::class;
    }
}
