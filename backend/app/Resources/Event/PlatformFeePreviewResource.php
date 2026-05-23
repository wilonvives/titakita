<?php

namespace TitaKita\Resources\Event;

use TitaKita\Resources\BaseResource;
use TitaKita\Services\Application\Handlers\EventSettings\DTO\PlatformFeePreviewResponseDTO;

/**
 * @mixin PlatformFeePreviewResponseDTO
 */
class PlatformFeePreviewResource extends BaseResource
{
    public function toArray($request): array
    {
        return [
            'event_currency' => $this->eventCurrency,
            'fee_currency' => $this->feeCurrency,
            'fixed_fee_original' => $this->fixedFeeOriginal,
            'fixed_fee_converted' => $this->fixedFeeConverted,
            'percentage_fee' => $this->percentageFee,
            'sample_price' => $this->samplePrice,
            'platform_fee' => $this->platformFee,
            'total' => $this->total,
        ];
    }
}
