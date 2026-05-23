<?php

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\StripePaymentDomainObject;
use TitaKita\Models\StripePayment;
use TitaKita\Repository\Interfaces\StripePaymentsRepositoryInterface;

/**
 * @extends BaseRepository<StripePaymentDomainObject>
 */
class StripePaymentsRepository extends BaseRepository implements StripePaymentsRepositoryInterface
{
    protected function getModel(): string
    {
        return StripePayment::class;
    }

    public function getDomainObject(): string
    {
        return StripePaymentDomainObject::class;
    }
}
