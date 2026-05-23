<?php

namespace TitaKita\DomainObjects\Enums;

enum TaxCalculationType
{
    use BaseEnum;

    case PERCENTAGE;
    case FIXED;
}
