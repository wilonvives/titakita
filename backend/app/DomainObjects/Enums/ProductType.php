<?php

namespace TitaKita\DomainObjects\Enums;

enum ProductType
{
    use BaseEnum;

    case TICKET;
    case GENERAL;
}
