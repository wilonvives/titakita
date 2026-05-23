<?php

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\StripeCustomerDomainObject;
use TitaKita\Models\StripeCustomer;
use TitaKita\Repository\Interfaces\StripeCustomerRepositoryInterface;

/**
 * @extends BaseRepository<StripeCustomerDomainObject>
 */
class StripeCustomerRepository extends BaseRepository implements StripeCustomerRepositoryInterface
{
    protected function getModel(): string
    {
        return StripeCustomer::class;
    }

    public function getDomainObject(): string
    {
        return StripeCustomerDomainObject::class;
    }
}
