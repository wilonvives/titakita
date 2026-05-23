<?php

namespace TitaKita\Services\Application\Handlers\Product;

use TitaKita\DomainObjects\ProductPriceDomainObject;
use TitaKita\DomainObjects\TaxAndFeesDomainObject;
use TitaKita\Http\DTO\QueryParamsDTO;
use TitaKita\Repository\Interfaces\ProductRepositoryInterface;
use TitaKita\Services\Domain\Product\ProductFilterService;
use Illuminate\Pagination\LengthAwarePaginator;

class GetProductsHandler
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductFilterService       $productFilterService,
    )
    {
    }

    public function handle(int $eventId, QueryParamsDTO $queryParamsDTO): LengthAwarePaginator
    {
        $productPaginator = $this->productRepository
            ->loadRelation(ProductPriceDomainObject::class)
            ->loadRelation(TaxAndFeesDomainObject::class)
            ->findByEventId($eventId, $queryParamsDTO);

        $filteredProducts = $this->productFilterService->filter(
            productsCategories: $productPaginator->getCollection(),
            hideSoldOutProducts: false,
            hideHiddenCategories: false,
        );

        $productPaginator->setCollection($filteredProducts);

        return $productPaginator;
    }
}
