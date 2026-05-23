<?php

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\ProductCategoryDomainObject;
use TitaKita\Http\DTO\QueryParamsDTO;
use TitaKita\Models\ProductCategory;
use TitaKita\Repository\Interfaces\ProductCategoryRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * @extends BaseRepository<ProductCategoryDomainObject>
 */
class ProductCategoryRepository extends BaseRepository implements ProductCategoryRepositoryInterface
{
    protected function getModel(): string
    {
        return ProductCategory::class;
    }

    public function getDomainObject(): string
    {
        return ProductCategoryDomainObject::class;
    }

    public function findByEventId(int $eventId, QueryParamsDTO $queryParamsDTO): Collection
    {
        $query = $this->model
            ->where('event_id', $eventId)
            ->with(['products']);

        // Apply filters from QueryParamsDTO, if needed
        if (!empty($queryParamsDTO->filter_fields)) {
            foreach ($queryParamsDTO->filter_fields as $filter) {
                $query->where($filter->field, $filter->operator ?? '=', $filter->value);
            }
        }

        $query->orderBy(
            $this->validateSortColumn($queryParamsDTO->sort_by, ProductCategoryDomainObject::class),
            $this->validateSortDirection($queryParamsDTO->sort_direction, ProductCategoryDomainObject::class),
        );

        return $query->get();
    }

    public function getNextOrder(int $eventId)
    {
        return $this->model
            ->where('event_id', $eventId)
            ->max('order') + 1;
    }
}
