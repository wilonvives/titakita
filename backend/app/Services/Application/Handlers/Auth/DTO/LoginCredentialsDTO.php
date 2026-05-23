<?php

namespace TitaKita\Services\Application\Handlers\Auth\DTO;

use TitaKita\DataTransferObjects\BaseDTO;

class LoginCredentialsDTO extends BaseDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly ?int $accountId = null,
    )
    {
    }
}
