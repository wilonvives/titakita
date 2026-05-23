<?php

namespace TitaKita\Services\Application\Handlers\EventSettings\DTO;

use TitaKita\DataTransferObjects\BaseDataObject;

class GetPlatformFeePreviewDTO extends BaseDataObject
{
    public function __construct(
        public readonly int   $eventId,
        public readonly float $price,
    ) {
    }
}
