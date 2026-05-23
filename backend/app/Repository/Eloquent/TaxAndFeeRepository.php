<?php

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\TaxAndFeesDomainObject;
use TitaKita\Models\TaxAndFee;
use TitaKita\Repository\Interfaces\TaxAndFeeRepositoryInterface;

/**
 * @extends BaseRepository<TaxAndFeesDomainObject>
 */
class TaxAndFeeRepository extends BaseRepository implements TaxAndFeeRepositoryInterface
{
    public function getDomainObject(): string
    {
        return TaxAndFeesDomainObject::class;
    }

    protected function getModel(): string
    {
        return TaxAndFee::class;
    }
}
