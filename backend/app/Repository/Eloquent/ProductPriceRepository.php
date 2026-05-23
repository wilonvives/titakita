<?php

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\ProductPriceDomainObject;
use TitaKita\Models\ProductPrice;
use TitaKita\Repository\Interfaces\ProductPriceRepositoryInterface;

/**
 * @extends BaseRepository<ProductPriceDomainObject>
 */
class ProductPriceRepository extends BaseRepository implements ProductPriceRepositoryInterface
{
    protected function getModel(): string
    {
        return ProductPrice::class;
    }

    public function getDomainObject(): string
    {
        return ProductPriceDomainObject::class;
    }
}
