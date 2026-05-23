<?php

namespace TitaKita\DomainObjects\Status;

use TitaKita\DomainObjects\Enums\BaseEnum;

enum EventLifecycleStatus
{
    use BaseEnum;

    case UPCOMING;
    case ENDED;
    case ONGOING;
}
