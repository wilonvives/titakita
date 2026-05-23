<?php

namespace TitaKita\DomainObjects\Status;

use TitaKita\DomainObjects\Enums\BaseEnum;

enum WaitlistEntryStatus
{
    use BaseEnum;

    case WAITING;
    case OFFERED;
    case PURCHASED;
    case CANCELLED;
    case OFFER_EXPIRED;
}
