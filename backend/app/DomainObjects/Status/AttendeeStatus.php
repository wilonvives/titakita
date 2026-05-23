<?php

namespace TitaKita\DomainObjects\Status;

use TitaKita\DomainObjects\Enums\BaseEnum;

enum AttendeeStatus
{
    use BaseEnum;

    case ACTIVE;
    case AWAITING_PAYMENT;
    case CANCELLED;
}
