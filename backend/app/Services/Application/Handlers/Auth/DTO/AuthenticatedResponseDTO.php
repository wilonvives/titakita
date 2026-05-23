<?php

namespace TitaKita\Services\Application\Handlers\Auth\DTO;

use TitaKita\DataTransferObjects\BaseDTO;
use TitaKita\DomainObjects\UserDomainObject;
use Illuminate\Support\Collection;

class AuthenticatedResponseDTO extends BaseDTO
{
    public function __construct(
        public ?string          $token,
        public int              $expiresIn,
        public Collection       $accounts,
        public UserDomainObject $user,
    )
    {
    }
}
