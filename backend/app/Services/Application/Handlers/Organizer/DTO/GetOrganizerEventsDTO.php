<?php

namespace TitaKita\Services\Application\Handlers\Organizer\DTO;

use TitaKita\DataTransferObjects\BaseDTO;
use TitaKita\Http\DTO\QueryParamsDTO;

class GetOrganizerEventsDTO extends BaseDTO
{
    public function __construct(
        public int            $organizerId,
        public int            $accountId,
        public QueryParamsDTO $queryParams
    )
    {
    }
}
