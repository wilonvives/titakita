<?php

namespace TitaKita\Services\Infrastructure\Jobs\Enum;

use TitaKita\DomainObjects\Enums\BaseEnum;

enum JobStatusEnum
{
    use BaseEnum;

    case IN_PROGRESS;
    case FINISHED;
    case FAILED;
    case NOT_FOUND;
}
