<?php

namespace TitaKita\DomainObjects\Status;

use TitaKita\DomainObjects\Enums\BaseEnum;

enum ProductStatus
{
    use BaseEnum;

    case ACTIVE;
    case INACTIVE;
}
