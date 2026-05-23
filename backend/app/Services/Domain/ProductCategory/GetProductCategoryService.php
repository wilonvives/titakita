<?php

namespace TitaKita\Services\Domain\ProductCategory;

use TitaKita\DomainObjects\Generated\ProductCategoryDomainObjectAbstract;
use TitaKita\DomainObjects\ProductCategoryDomainObject;
use TitaKita\DomainObjects\ProductDomainObject;
use TitaKita\Repository\Eloquent\Value\OrderAndDirection;
use TitaKita\Repository\Eloquent\Value\Relationship;
use TitaKita\Repository\Interfaces\ProductCategoryRepositoryInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class GetProductCategoryService
{
    public function __construct(
        private readonly ProductCategoryRepositoryInterface $productCategoryRepository,
    )
    {
    }

    public function getCategory(int $categoryId, int $eventId): ProductCategoryDomainObject
    {
        $category = $this->productCategoryRepository
            ->loadRelation(new Relationship(
                domainObject: ProductDomainObject::class,
                orderAndDirections: [
                    new OrderAndDirection(
                        order: ProductCategoryDomainObjectAbstract::ORDER,
                    ),
                ],
            ))
            ->findFirstWhere(
                where: [
                    'id' => $categoryId,
                    'event_id' => $eventId,
                ]
            );

        if (!$category) {
            throw new ResourceNotFoundException(
                __('The product category with ID :id was not found.', ['id' => $categoryId])
            );
        }

        return $category;
    }
}
