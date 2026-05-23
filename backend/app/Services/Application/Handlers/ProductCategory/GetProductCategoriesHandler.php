<?php

namespace TitaKita\Services\Application\Handlers\ProductCategory;

use TitaKita\DomainObjects\Generated\ProductCategoryDomainObjectAbstract;
use TitaKita\DomainObjects\Generated\ProductDomainObjectAbstract;
use TitaKita\DomainObjects\ProductDomainObject;
use TitaKita\DomainObjects\ProductPriceDomainObject;
use TitaKita\DomainObjects\TaxAndFeesDomainObject;
use TitaKita\Repository\Eloquent\Value\OrderAndDirection;
use TitaKita\Repository\Eloquent\Value\Relationship;
use TitaKita\Repository\Interfaces\ProductCategoryRepositoryInterface;
use TitaKita\Services\Domain\Product\ProductFilterService;
use Illuminate\Support\Collection;

class GetProductCategoriesHandler
{
    public function __construct(
        private readonly ProductCategoryRepositoryInterface $productCategoryRepository,
        private readonly ProductFilterService               $productFilterService,
    )
    {
    }

    public function handle(int $eventId): Collection
    {
        $categories = $this->productCategoryRepository
            ->loadRelation(new Relationship(
                domainObject: ProductDomainObject::class,
                nested: [
                    new Relationship(ProductPriceDomainObject::class),
                    new Relationship(TaxAndFeesDomainObject::class),
                ],
                orderAndDirections: [
                    new OrderAndDirection(
                        order: ProductDomainObjectAbstract::ORDER,
                    ),
                ],
            ))
            ->findWhere(
                where: [
                    'event_id' => $eventId,
                ],
                orderAndDirections: [
                    new OrderAndDirection(
                        order: ProductCategoryDomainObjectAbstract::ORDER,
                    ),
                ],
            );

        return $this->productFilterService->filter(
            productsCategories: $categories,
            hideSoldOutProducts: false,
            hideHiddenCategories: false,
        );
    }
}
