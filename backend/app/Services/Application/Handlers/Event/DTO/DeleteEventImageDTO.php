<?php

namespace TitaKita\Services\Application\Handlers\Event\DTO;

use TitaKita\DataTransferObjects\BaseDTO;

class DeleteEventImageDTO extends BaseDTO
{
    public function __construct(
        public int $eventId,
        public int $imageId,
    )
    {
    }
}
