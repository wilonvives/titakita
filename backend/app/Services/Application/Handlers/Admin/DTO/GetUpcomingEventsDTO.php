<?php

namespace TitaKita\Services\Application\Handlers\Admin\DTO;

use TitaKita\DataTransferObjects\BaseDataObject;

class GetUpcomingEventsDTO extends BaseDataObject
{
    public function __construct(
        public readonly int $perPage = 20,
    )
    {
    }
}
