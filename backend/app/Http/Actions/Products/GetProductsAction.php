<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Products;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\ProductDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\Product\ProductResource;
use TitaKita\Services\Application\Handlers\Product\GetProductsHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetProductsAction extends BaseAction
{
    public function __construct(
        private readonly GetProductsHandler $getProductsHandler,
    )
    {
    }

    public function __invoke(int $eventId, Request $request): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $products = $this->getProductsHandler->handle(
            eventId: $eventId,
            queryParamsDTO: $this->getPaginationQueryParams($request),
        );

        return $this->filterableResourceResponse(
            resource: ProductResource::class,
            data: $products,
            domainObject: ProductDomainObject::class
        );
    }
}
