<?php

namespace TitaKita\Services\Domain\Payment\Stripe\DTOs;

use TitaKita\DataTransferObjects\BaseDataObject;

class StripePayoutCreationDTO extends BaseDataObject
{
    public function __construct(
        public readonly string $payoutId,
        public readonly ?string $stripePlatform,
        public readonly ?int $amountMinor,
        public readonly ?string $currency,
        public readonly ?\DateTimeInterface $payoutDate,
        public readonly ?string $status,
        public readonly ?array $metadata,
    ) {
    }
}

