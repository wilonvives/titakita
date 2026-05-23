<?php

namespace TitaKita\Services\Application\Handlers\User;

use TitaKita\DomainObjects\AccountDomainObject;
use TitaKita\DomainObjects\Enums\Role;
use TitaKita\DomainObjects\Status\UserStatus;
use TitaKita\DomainObjects\UserDomainObject;
use TitaKita\Exceptions\ResourceConflictException;
use TitaKita\Exceptions\UnauthorizedException;
use TitaKita\Repository\Interfaces\AccountRepositoryInterface;
use TitaKita\Repository\Interfaces\UserRepositoryInterface;
use TitaKita\Services\Application\Handlers\User\DTO\CreateUserDTO;
use TitaKita\Services\Domain\Account\AccountUserAssociationService;
use TitaKita\Services\Domain\User\SendUserInvitationService;
use Illuminate\Database\DatabaseManager;
use Throwable;

readonly class CreateUserHandler
{
    public function __construct(
        private UserRepositoryInterface       $userRepository,
        private AccountRepositoryInterface    $accountRepository,
        private SendUserInvitationService     $sendUserInvitationService,
        private AccountUserAssociationService $accountUserAssociationService,
        private DatabaseManager               $databaseManager,
    )
    {
    }

    /**
     * @throws ResourceConflictException
     * @throws Throwable
     * @throws UnauthorizedException
     */
    public function handle(CreateUserDTO $userData): UserDomainObject
    {
        if ($userData->role === Role::SUPERADMIN) {
            throw new UnauthorizedException(
                __('SUPERADMIN users cannot be created through the application')
            );
        }

        return $this->databaseManager->transaction(function () use ($userData) {
            $existingUser = $this->getExistingUser($userData);

            $authenticatedAccount = $this->accountRepository->findById($userData->account_id);

            $invitedUser = $existingUser ?? $this->createUser($userData, $authenticatedAccount);

            $invitedUser->setCurrentAccountUser($this->accountUserAssociationService->associate(
                user: $invitedUser,
                account: $authenticatedAccount,
                role: $userData->role,
                status: UserStatus::INVITED,
                invitedByUserId: $userData->invited_by,
            ));

            $this->sendUserInvitationService->sendInvitation($invitedUser, $authenticatedAccount->getId());

            return $invitedUser;
        });

    }

    private function createUser(CreateUserDTO $userData, AccountDomainObject $authenticatedAccount): UserDomainObject
    {
        return $this->userRepository
            ->create([
                'first_name' => $userData->first_name,
                'last_name' => $userData->last_name,
                'email' => strtolower($userData->email),
                'password' => 'invited', // initially, a user is in an invited state, so they don't have a password
                'timezone' => $authenticatedAccount->getTimezone(),
            ]);
    }

    /**
     * @throws ResourceConflictException
     */
    private function getExistingUser(CreateUserDTO $userData): ?UserDomainObject
    {
        $existingUser = $this->userRepository
            ->loadRelation(AccountDomainObject::class)
            ->findFirstWhere([
                'email' => $userData->email,
            ]);

        if ($existingUser === null) {
            return null;
        }

        if ($existingUser->accounts->some(fn($account) => $account->getId() === $userData->account_id)) {
            throw new ResourceConflictException(
                __('The email :email already exists on this account', [
                    'email' => $userData->email,
                ])
            );
        }

        return $existingUser;
    }
}
