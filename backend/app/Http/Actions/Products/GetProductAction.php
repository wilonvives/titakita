<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Products;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\Generated\ProductDomainObjectAbstract;
use TitaKita\DomainObjects\ProductPriceDomainObject;
use TitaKita\DomainObjects\TaxAndFeesDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Repository\Interfaces\ProductRepositoryInterface;
use TitaKita\Resources\Product\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class GetProductAction extends BaseAction
{
    private ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function __invoke(int $eventId, int $productId): JsonResponse|Response
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $product = $this->productRepository
            ->loadRelation(TaxAndFeesDomainObject::class)
            ->loadRelation(ProductPriceDomainObject::class)
            ->findFirstWhere([
                ProductDomainObjectAbstract::EVENT_ID => $eventId,
                ProductDomainObjectAbstract::ID => $productId,
            ]);

        if ($product === null) {
            return $this->notFoundResponse();
        }

        return $this->resourceResponse(ProductResource::class, $product);
    }
}
