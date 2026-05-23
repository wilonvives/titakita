<?php

namespace TitaKita\Services\Application\Handlers\Event\DTO;

use TitaKita\DataTransferObjects\BaseDTO;

class UpdateEventStatusDTO extends BaseDTO
{
    public function __construct(
        public string $status,
        public int $eventId,
        public int $accountId,
    )
    {
    }
}
