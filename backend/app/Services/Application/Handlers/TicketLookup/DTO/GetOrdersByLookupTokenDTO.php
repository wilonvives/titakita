<?php

namespace TitaKita\Services\Application\Handlers\TicketLookup\DTO;

use TitaKita\DataTransferObjects\BaseDataObject;

class GetOrdersByLookupTokenDTO extends BaseDataObject
{
    public function __construct(
        public readonly string $token,
    ) {
    }
}
