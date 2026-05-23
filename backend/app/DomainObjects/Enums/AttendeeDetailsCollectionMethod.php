<?php

namespace TitaKita\DomainObjects\Enums;

enum AttendeeDetailsCollectionMethod: string
{
    use BaseEnum;

    case PER_TICKET = 'PER_TICKET';
    case PER_ORDER = 'PER_ORDER';
}
