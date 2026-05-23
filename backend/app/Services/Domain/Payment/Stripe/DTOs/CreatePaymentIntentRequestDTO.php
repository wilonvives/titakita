<?php

namespace TitaKita\Services\Domain\Payment\Stripe\DTOs;

use TitaKita\DataTransferObjects\BaseDTO;
use TitaKita\DomainObjects\AccountDomainObject;
use TitaKita\DomainObjects\AccountVatSettingDomainObject;
use TitaKita\DomainObjects\OrderDomainObject;
use TitaKita\Values\MoneyValue;

class CreatePaymentIntentRequestDTO extends BaseDTO
{
    public function __construct(
        public readonly MoneyValue                     $amount,
        public readonly string                         $currencyCode,
        public readonly AccountDomainObject            $account,
        public readonly OrderDomainObject              $order,
        public readonly ?string                        $stripeAccountId = null,
        public readonly ?AccountVatSettingDomainObject $vatSettings = null,
        public readonly ?string                        $description = null,
    )
    {
    }
}
