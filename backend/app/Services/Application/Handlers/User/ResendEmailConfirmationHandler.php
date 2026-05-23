<?php

namespace TitaKita\Services\Application\Handlers\User;

use TitaKita\DomainObjects\UserDomainObject;
use TitaKita\Services\Domain\User\EmailConfirmationService;

class ResendEmailConfirmationHandler
{
    public function __construct(
        private readonly EmailConfirmationService $emailConfirmationService,
    )
    {
    }

    public function handle(UserDomainObject $user, int $accountId): void
    {
        $this->emailConfirmationService->sendConfirmation($user, $accountId);
    }
}
