<?php

namespace TitaKita\DomainObjects\DTO;

class AccountApplicationFeeDTO
{
    public function __construct(
        public readonly float $percentageFee,
        public readonly float $fixedFee,
    )
    {
    }
}
