<?php

namespace TitaKita\Services\Domain\Product;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\ProductPriceDomainObject;
use TitaKita\Helper\DateHelper;
use TitaKita\Repository\Eloquent\ProductPriceRepository;
use Illuminate\Support\Collection;

class ProductPriceCreateService
{
    public function __construct(
        private readonly ProductPriceRepository $productPriceRepository,
    )
    {
    }

    public function createPrices(
        int               $productId,
        Collection        $prices,
        EventDomainObject $event,
    ): Collection
    {
        return (new Collection($prices->map(fn(ProductPriceDomainObject $price, int $index) => $this->productPriceRepository->create([
            'product_id' => $productId,
            'price' => $price->getPrice(),
            'label' => $price->getLabel(),
            'sale_start_date' => $price->getSaleStartDate()
                ? DateHelper::convertToUTC($price->getSaleStartDate(), $event->getTimezone())
                : null,
            'sale_end_date' => $price->getSaleEndDate()
                ? DateHelper::convertToUTC($price->getSaleEndDate(), $event->getTimezone())
                : null,
            'initial_quantity_available' => $price->getInitialQuantityAvailable(),
            'is_hidden' => $price->getIsHidden(),
            'order' => $index + 1,
        ]))));
    }
}
