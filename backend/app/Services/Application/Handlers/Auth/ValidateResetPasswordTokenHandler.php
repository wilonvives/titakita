<?php

namespace TitaKita\Services\Application\Handlers\Auth;

use TitaKita\DomainObjects\PasswordResetTokenDomainObject;
use TitaKita\Exceptions\InvalidPasswordResetTokenException;
use TitaKita\Services\Domain\Auth\ResetPasswordTokenValidateService;

class ValidateResetPasswordTokenHandler
{
    private ResetPasswordTokenValidateService $passwordTokenValidateService;

    public function __construct(ResetPasswordTokenValidateService $passwordTokenValidateService)
    {
        $this->passwordTokenValidateService = $passwordTokenValidateService;
    }

    /**
     * @throws InvalidPasswordResetTokenException
     */
    public function handle(string $token): PasswordResetTokenDomainObject
    {
        return $this->passwordTokenValidateService->validateAndFetchToken($token);
    }
}
