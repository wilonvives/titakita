<?php

namespace TitaKita\Services\Application\Handlers\TicketLookup\DTO;

use TitaKita\DataTransferObjects\BaseDataObject;

class SendTicketLookupEmailDTO extends BaseDataObject
{
    public function __construct(
        public readonly string $email,
    ) {
    }
}
