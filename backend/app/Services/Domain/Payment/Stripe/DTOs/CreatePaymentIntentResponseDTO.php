<?php

namespace TitaKita\Services\Domain\Payment\Stripe\DTOs;

use TitaKita\DomainObjects\Enums\StripePlatform;
use TitaKita\Services\Domain\Order\DTO\ApplicationFeeValuesDTO;

readonly class CreatePaymentIntentResponseDTO
{
    public function __construct(
        public ?string                  $paymentIntentId = null,
        public ?string                  $clientSecret = null,
        public ?string                  $accountId = null,
        public ?string                  $error = null,
        public ?ApplicationFeeValuesDTO $applicationFeeData = null,
        public ?StripePlatform          $stripePlatform = null,
        public ?string                  $publicKey = null,
    )
    {
    }
}
