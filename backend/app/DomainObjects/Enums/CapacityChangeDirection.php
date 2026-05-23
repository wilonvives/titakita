<?php

namespace TitaKita\DomainObjects\Enums;

enum CapacityChangeDirection
{
    use BaseEnum;

    case INCREASED;
    case DECREASED;
}
