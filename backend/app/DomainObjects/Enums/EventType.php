<?php

namespace TitaKita\DomainObjects\Enums;

enum EventType: string
{
    use BaseEnum;

    case EVENT = 'event';
    case BOOKING = 'booking';
}
