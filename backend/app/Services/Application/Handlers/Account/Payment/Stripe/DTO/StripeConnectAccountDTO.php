<?php

namespace TitaKita\Services\Application\Handlers\Account\Payment\Stripe\DTO;

use TitaKita\DataTransferObjects\BaseDataObject;
use TitaKita\DomainObjects\Enums\StripePlatform;

class StripeConnectAccountDTO extends BaseDataObject
{
    public function __construct(
        public readonly ?string         $stripeAccountId = null,
        public readonly ?string         $connectUrl = null,
        public readonly bool            $isSetupComplete = false,
        public readonly ?StripePlatform $platform = null,
        public readonly ?string         $accountType = null,
        public readonly bool            $isPrimary = false,
        public readonly ?string         $country = null,
    )
    {
    }
}
