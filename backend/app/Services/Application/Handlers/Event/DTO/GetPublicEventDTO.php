<?php

namespace TitaKita\Services\Application\Handlers\Event\DTO;

use TitaKita\DataTransferObjects\BaseDTO;

class GetPublicEventDTO extends BaseDTO
{
    public function __construct(
        public int     $eventId,
        public bool    $isAuthenticated,
        public ?string $ipAddress = null,
        public ?string $promoCode = null,
    )
    {
    }
}
