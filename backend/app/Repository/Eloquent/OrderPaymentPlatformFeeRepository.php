<?php

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\OrderPaymentPlatformFeeDomainObject;
use TitaKita\Models\OrderPaymentPlatformFee;
use TitaKita\Repository\Interfaces\OrderPaymentPlatformFeeRepositoryInterface;

/**
 * @extends BaseRepository<OrderPaymentPlatformFeeDomainObject>
 */
class OrderPaymentPlatformFeeRepository extends BaseRepository implements OrderPaymentPlatformFeeRepositoryInterface
{
    protected function getModel(): string
    {
        return OrderPaymentPlatformFee::class;
    }

    public function getDomainObject(): string
    {
        return OrderPaymentPlatformFeeDomainObject::class;
    }
}
