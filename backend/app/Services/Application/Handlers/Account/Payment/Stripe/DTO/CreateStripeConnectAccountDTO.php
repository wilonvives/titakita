<?php

namespace TitaKita\Services\Application\Handlers\Account\Payment\Stripe\DTO;

use TitaKita\DataTransferObjects\BaseDataObject;
use TitaKita\DomainObjects\Enums\StripePlatform;

class CreateStripeConnectAccountDTO extends BaseDataObject
{
    public function __construct(
        public readonly int                 $accountId,
        public readonly StripePlatform|null $platform = null,
    )
    {
    }
}
