<?php

namespace TitaKita\Services\Application\Handlers\Event\DTO;

use TitaKita\DataTransferObjects\BaseDTO;
use TitaKita\Http\DTO\QueryParamsDTO;

class GetEventsDTO extends BaseDTO
{
    public function __construct(
        public int $accountId,
        public QueryParamsDTO $queryParams,
    )
    {
    }
}
