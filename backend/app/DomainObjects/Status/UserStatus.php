<?php

namespace TitaKita\DomainObjects\Status;

use TitaKita\DomainObjects\Enums\BaseEnum;

enum UserStatus
{
    use BaseEnum;

    case ACTIVE;
    case INVITED;
    case INACTIVE;
}
