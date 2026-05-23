<?php

namespace TitaKita\Services\Application\Handlers\Organizer\DTO;

use TitaKita\DataTransferObjects\BaseDTO;

class DeleteOrganizerDTO extends BaseDTO
{
    public function __construct(
        public int $organizerId,
        public int $accountId,
    )
    {
    }
}
