<?php

namespace TitaKita\DomainObjects\Status;

use TitaKita\DomainObjects\Enums\BaseEnum;

enum MessageStatus
{
    use BaseEnum;

    case PENDING_REVIEW;
    case PROCESSING;
    case SENT;
    case FAILED;
    case SCHEDULED;
    case CANCELLED;
}
