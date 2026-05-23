<?php

namespace TitaKita\Http\Actions\ProductCategories;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\ProductCategory\ProductCategoryResource;
use TitaKita\Services\Application\Handlers\ProductCategory\GetProductCategoriesHandler;
use Illuminate\Http\JsonResponse;

class GetProductCategoriesAction extends BaseAction
{
    public function __construct(
        private readonly GetProductCategoriesHandler $getProductCategoriesHandler,
    )
    {
    }

    public function __invoke(int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $categories = $this->getProductCategoriesHandler->handle($eventId);

        return $this->resourceResponse(
            resource: ProductCategoryResource::class,
            data: $categories,
        );
    }
}
