<?php

namespace TitaKita\Services\Application\Handlers\Admin\DTO;

use TitaKita\DataTransferObjects\BaseDataObject;
use TitaKita\DomainObjects\Generated\EventDomainObjectAbstract;

class GetAllEventsDTO extends BaseDataObject
{
    public function __construct(
        public readonly int     $perPage = 20,
        public readonly ?string $search = null,
        public readonly ?string $sortBy = EventDomainObjectAbstract::START_DATE,
        public readonly ?string $sortDirection = 'desc',
    )
    {
    }
}
