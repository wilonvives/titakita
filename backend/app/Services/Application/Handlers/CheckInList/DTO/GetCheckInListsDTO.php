<?php

namespace TitaKita\Services\Application\Handlers\CheckInList\DTO;

use TitaKita\DataTransferObjects\BaseDTO;
use TitaKita\Http\DTO\QueryParamsDTO;

class GetCheckInListsDTO extends BaseDTO
{
    public function __construct(
        public int            $eventId,
        public QueryParamsDTO $queryParams,
    )
    {
    }
}
