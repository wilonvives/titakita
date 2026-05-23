<?php

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\CapacityAssignmentDomainObject;
use TitaKita\DomainObjects\Generated\CapacityAssignmentDomainObjectAbstract;
use TitaKita\Http\DTO\QueryParamsDTO;
use TitaKita\Models\CapacityAssignment;
use TitaKita\Repository\Interfaces\CapacityAssignmentRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends BaseRepository<CapacityAssignmentDomainObject>
 */
class CapacityAssignmentRepository extends BaseRepository implements CapacityAssignmentRepositoryInterface
{
    protected function getModel(): string
    {
        return CapacityAssignment::class;
    }

    public function getDomainObject(): string
    {
        return CapacityAssignmentDomainObject::class;
    }

    public function findByEventId(int $eventId, QueryParamsDTO $params): LengthAwarePaginator
    {
        $where = [
            [CapacityAssignmentDomainObjectAbstract::EVENT_ID, '=', $eventId]
        ];

        if (!empty($params->query)) {
            $where[] = static function (Builder $builder) use ($params) {
                $builder
                    ->where(CapacityAssignmentDomainObjectAbstract::NAME, 'ilike', '%' . $params->query . '%');
            };
        }

        $this->model = $this->model->orderBy(
            $this->validateSortColumn($params->sort_by, CapacityAssignmentDomainObject::class),
            $this->validateSortDirection($params->sort_direction, CapacityAssignmentDomainObject::class),
        );

        return $this->paginateWhere(
            where: $where,
            limit: $params->per_page,
            page: $params->page,
        );
    }
}
