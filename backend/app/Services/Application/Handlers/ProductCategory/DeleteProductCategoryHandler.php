<?php

namespace TitaKita\Services\Application\Handlers\ProductCategory;

use TitaKita\Exceptions\CannotDeleteEntityException;
use TitaKita\Services\Domain\ProductCategory\DeleteProductCategoryService;
use Throwable;

class DeleteProductCategoryHandler
{
    public function __construct(
        private readonly DeleteProductCategoryService $deleteProductCategoryService,
    )
    {
    }

    /**
     * @throws Throwable
     * @throws CannotDeleteEntityException
     */
    public function handle(int $productCategoryId, int $eventId): void
    {
        $this->deleteProductCategoryService->deleteProductCategory($productCategoryId, $eventId);
    }
}
