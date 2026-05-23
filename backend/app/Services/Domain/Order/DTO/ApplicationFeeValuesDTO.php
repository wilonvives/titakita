<?php

namespace TitaKita\Services\Domain\Order\DTO;

use TitaKita\DataTransferObjects\BaseDataObject;
use TitaKita\Values\MoneyValue;

class ApplicationFeeValuesDTO extends BaseDataObject
{
    public function __construct(
        public MoneyValue  $grossApplicationFee,
        public MoneyValue  $netApplicationFee,
        public ?float      $applicationFeeVatRate = null,
        public ?MoneyValue $applicationFeeVatAmount = null,
    )
    {
    }
}
