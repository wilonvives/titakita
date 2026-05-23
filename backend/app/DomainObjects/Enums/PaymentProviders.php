<?php

namespace TitaKita\DomainObjects\Enums;

enum PaymentProviders: string
{
    use BaseEnum;

    case STRIPE = 'STRIPE';
    case OFFLINE = 'OFFLINE';
}
