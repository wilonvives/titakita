<?php

namespace TitaKita\Repository\Eloquent;

use TitaKita\Repository\Interfaces\StripePayoutsRepositoryInterface;
use TitaKita\Models\StripePayout;
use TitaKita\DomainObjects\StripePayoutDomainObject;

/**
 * @extends BaseRepository<StripePayoutDomainObject>
 */
class StripePayoutsRepository extends BaseRepository implements StripePayoutsRepositoryInterface
{
    protected function getModel(): string
    {
        return StripePayout::class;
    }

    public function getDomainObject(): string
    {
        return StripePayoutDomainObject::class;
    }
}
