<?php

namespace TitaKita\Services\Application\Handlers\User\DTO;

use TitaKita\DataTransferObjects\BaseDTO;

class CancelEmailChangeDTO extends BaseDTO
{
    public function __construct(
        public int $userId,
        public int $accountId,
    )
    {
    }
}
