<?php

namespace TitaKita\Repository\Interfaces;

use TitaKita\DomainObjects\CapacityAssignmentDomainObject;
use TitaKita\Http\DTO\QueryParamsDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @extends RepositoryInterface<CapacityAssignmentDomainObject>
 */
interface CapacityAssignmentRepositoryInterface extends RepositoryInterface
{
    public function findByEventId(int $eventId, QueryParamsDTO $params): LengthAwarePaginator;
}
