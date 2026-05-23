<?php

namespace TitaKita\Services\Application\Handlers\TaxAndFee\DTO;

use TitaKita\DataTransferObjects\BaseDTO;

class DeleteTaxDTO extends BaseDTO
{
    public function __construct(
        public readonly int $taxId,
        public readonly int $accountId,
    )
    {
    }
}
