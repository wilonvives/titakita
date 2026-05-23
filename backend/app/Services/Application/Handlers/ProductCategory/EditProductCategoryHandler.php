<?php

namespace TitaKita\Services\Application\Handlers\ProductCategory;

use TitaKita\DomainObjects\ProductCategoryDomainObject;
use TitaKita\Repository\Interfaces\ProductCategoryRepositoryInterface;
use TitaKita\Services\Application\Handlers\ProductCategory\DTO\UpsertProductCategoryDTO;
use TitaKita\Services\Infrastructure\HtmlPurifier\HtmlPurifierService;

class EditProductCategoryHandler
{
    public function __construct(
        private readonly ProductCategoryRepositoryInterface $productCategoryRepository,
        private readonly HtmlPurifierService $purifier,
    )
    {
    }

    public function handle(UpsertProductCategoryDTO $dto): ProductCategoryDomainObject
    {
        $this->productCategoryRepository->updateWhere(
            attributes: [
                'name' => $dto->name,
                'is_hidden' => $dto->is_hidden,
                'description' => $this->purifier->purify($dto->description),
                'no_products_message' => $dto->no_products_message ?? __('There are no products available in this category'),
            ],
            where: [
                'id' => $dto->product_category_id,
                'event_id' => $dto->event_id,
            ],
        );

        return $this->productCategoryRepository->findFirstWhere([
            'id' => $dto->product_category_id,
            'event_id' => $dto->event_id,
        ]);
    }
}
