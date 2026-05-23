<?php

namespace TitaKita\Http\Actions\ProductCategories;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\ProductCategory\UpsertProductCategoryRequest;
use TitaKita\Http\ResponseCodes;
use TitaKita\Resources\ProductCategory\ProductCategoryResource;
use TitaKita\Services\Application\Handlers\ProductCategory\CreateProductCategoryHandler;
use TitaKita\Services\Application\Handlers\ProductCategory\DTO\UpsertProductCategoryDTO;
use Illuminate\Http\JsonResponse;

class CreateProductCategoryAction extends BaseAction
{
    public function __construct(
        private readonly CreateProductCategoryHandler $handler
    )
    {
    }

    public function __invoke(UpsertProductCategoryRequest $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $productCategory = $this->handler->handle(new UpsertProductCategoryDTO(
            name: $request->validated('name'),
            description: $request->validated('description'),
            is_hidden: $request->validated('is_hidden'),
            event_id: $eventId,
            no_products_message: $request->validated('no_products_message'),
        ));

        return $this->resourceResponse(
            resource: ProductCategoryResource::class,
            data: $productCategory,
            statusCode: ResponseCodes::HTTP_CREATED,
        );
    }
}
