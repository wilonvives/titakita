<?php

namespace TitaKita\DomainObjects\Status;

use TitaKita\DomainObjects\Enums\BaseEnum;

enum WebhookStatus: string
{
    use BaseEnum;

    case ENABLED = 'ENABLED';
    case PAUSED = 'PAUSED';
}
