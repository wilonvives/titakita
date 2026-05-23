<?php

declare(strict_types=1);

namespace TitaKita\DomainObjects\Status;

use TitaKita\DomainObjects\Enums\BaseEnum;

enum AffiliateStatus: string
{
    use BaseEnum;

    case ACTIVE = 'ACTIVE';
    case INACTIVE = 'INACTIVE';
}
