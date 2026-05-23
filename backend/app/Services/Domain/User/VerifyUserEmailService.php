<?php

namespace TitaKita\Services\Domain\User;

use TitaKita\DomainObjects\UserDomainObject;
use TitaKita\Repository\Interfaces\AccountRepositoryInterface;
use TitaKita\Repository\Interfaces\AccountUserRepositoryInterface;
use TitaKita\Repository\Interfaces\UserRepositoryInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class VerifyUserEmailService
{
    public function __construct(
        private readonly UserRepositoryInterface        $userRepository,
        private readonly AccountRepositoryInterface     $accountRepository,
        private readonly AccountUserRepositoryInterface $accountUserRepository,
    )
    {
    }

    public function markEmailAsVerified(UserDomainObject $user, int $accountId): void
    {
        $this->userRepository->updateWhere(
            attributes: [
                'email_verified_at' => now(),
            ],
            where: [
                'id' => $user->getId(),
            ],
        );

        $accountUser = $this->accountUserRepository->findFirstWhere(
            where: [
                'user_id' => $user->getId(),
                'account_id' => $accountId,
            ]
        );

        if ($accountUser === null) {
            throw new ResourceNotFoundException();
        }

        // If this is the account owner, mark the account as verified
        if ($accountUser->getIsAccountOwner()) {
            $this->accountRepository->updateWhere(
                attributes: [
                    'account_verified_at' => now(),
                ],
                where: [
                    'id' => $accountId,
                ]
            );
        }
    }
}
