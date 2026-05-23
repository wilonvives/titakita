<?php

namespace TitaKita\DomainObjects\Status;

use TitaKita\DomainObjects\Enums\BaseEnum;

enum OrganizerStatus: string
{
    use BaseEnum;

    case DRAFT = 'DRAFT';
    case LIVE = 'LIVE';
    case ARCHIVED = 'ARCHIVED';
}
