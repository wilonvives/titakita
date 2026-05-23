<?php

namespace TitaKita\Services\Application\Handlers\Event\DTO;

use TitaKita\DataTransferObjects\BaseDTO;
use TitaKita\Http\DTO\QueryParamsDTO;

class GetPublicOrganizerEventsDTO extends BaseDTO
{
    public function __construct(
        public int            $organizerId,
        public QueryParamsDTO $queryParams,
        public ?int           $authenticatedAccountId = null,
    )
    {
    }
}
