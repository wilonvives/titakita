<?php

namespace TitaKita\Services\Application\Handlers\User\DTO;

use TitaKita\DataTransferObjects\BaseDTO;

class ConfirmEmailChangeDTO extends BaseDTO
{
    public function __construct(
        public string $token,
        public int $accountId,
    )
    {
    }
}
