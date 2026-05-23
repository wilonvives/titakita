<?php

namespace TitaKita\Services\Application\Handlers\CapacityAssignment\DTO;

use TitaKita\DataTransferObjects\BaseDTO;
use TitaKita\Http\DTO\QueryParamsDTO;

class GetCapacityAssignmentsDTO extends BaseDTO
{
    public function __construct(
        public int            $eventId,
        public QueryParamsDTO $queryParams,
    )
    {
    }
}
