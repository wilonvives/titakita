<?php

namespace TitaKita\DomainObjects\Status;

use TitaKita\DomainObjects\Enums\BaseEnum;

enum EventStatus
{
    use BaseEnum;

    case DRAFT;
    case LIVE;
    case ARCHIVED;
}
