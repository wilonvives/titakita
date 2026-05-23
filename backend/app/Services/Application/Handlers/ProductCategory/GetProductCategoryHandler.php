<?php

namespace TitaKita\Services\Application\Handlers\ProductCategory;

use TitaKita\DomainObjects\Generated\ProductDomainObjectAbstract;
use TitaKita\DomainObjects\ProductCategoryDomainObject;
use TitaKita\DomainObjects\ProductDomainObject;
use TitaKita\DomainObjects\ProductPriceDomainObject;
use TitaKita\DomainObjects\TaxAndFeesDomainObject;
use TitaKita\Repository\Eloquent\Value\OrderAndDirection;
use TitaKita\Repository\Eloquent\Value\Relationship;
use TitaKita\Repository\Interfaces\ProductCategoryRepositoryInterface;

class GetProductCategoryHandler
{
    public function __construct(
        private readonly ProductCategoryRepositoryInterface $productCategoryRepository,
    )
    {
    }

    public function handle(int $eventId, int $productCategoryId): ProductCategoryDomainObject
    {
        return $this->productCategoryRepository
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
            ->findFirstWhere(
                where: [
                    'event_id' => $eventId,
                    'id' => $productCategoryId,
                ]
            );
    }
}
