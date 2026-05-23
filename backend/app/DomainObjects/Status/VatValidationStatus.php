<?php

declare(strict_types=1);

namespace TitaKita\DomainObjects\Status;

use TitaKita\DomainObjects\Enums\BaseEnum;

enum VatValidationStatus: string
{
    use BaseEnum;

    case PENDING = 'PENDING';
    case VALIDATING = 'VALIDATING';
    case VALID = 'VALID';
    case INVALID = 'INVALID';
    case FAILED = 'FAILED';
}
