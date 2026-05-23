<?php

namespace TitaKita\DomainObjects\Enums;

enum CapacityAssignmentAppliesTo
{
    use BaseEnum;

    case PRODUCTS;
    case EVENT;
}
