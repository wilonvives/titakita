<?php

namespace TitaKita\DomainObjects\Status;

use TitaKita\DomainObjects\Enums\BaseEnum;

enum InvoiceStatus
{
    use BaseEnum;

    case UNPAID;
    case PAID;
    case VOID;
}
