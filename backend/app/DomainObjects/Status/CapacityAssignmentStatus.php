<?php

namespace TitaKita\DomainObjects\Status;

use TitaKita\DomainObjects\Enums\BaseEnum;

enum CapacityAssignmentStatus
{
    use BaseEnum;

    case ACTIVE;
    case INACTIVE;
}
