<?php

namespace TitaKita\DomainObjects\Enums;

enum PriceDisplayMode
{
    use BaseEnum;

    case INCLUSIVE;
    case EXCLUSIVE;
}
