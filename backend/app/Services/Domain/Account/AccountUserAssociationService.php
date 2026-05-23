<?php

namespace TitaKita\Services\Domain\Account;

use TitaKita\DomainObjects\AccountDomainObject;
use TitaKita\DomainObjects\AccountUserDomainObject;
use TitaKita\DomainObjects\Enums\Role;
use TitaKita\DomainObjects\Status\UserStatus;
use TitaKita\DomainObjects\UserDomainObject;
use TitaKita\Exceptions\UnauthorizedException;
use TitaKita\Repository\Interfaces\AccountUserRepositoryInterface;

readonly class AccountUserAssociationService
{
    public function __construct(
        private AccountUserRepositoryInterface $accountUserRepository,
    )
    {
    }

    public function associate(
        UserDomainObject    $user,
        AccountDomainObject $account,
        Role                $role,
        ?UserStatus         $status = null,
        ?int                $invitedByUserId = null,
        bool                $isAccountOwner = false,
    ): AccountUserDomainObject
    {
        if ($role === Role::SUPERADMIN) {
            throw new UnauthorizedException(__('Cannot associate a user with SUPERADMIN role to an account'));
        }

        $data = [
            'user_id' => $user->getId(),
            'account_id' => $account->getId(),
            'role' => $role->name,
            'is_account_owner' => $isAccountOwner,
        ];

        if ($status !== null) {
            $data['status'] = $status->name;
        }

        if ($invitedByUserId !== null) {
            $data['invited_by_user_id'] = $invitedByUserId;
        }

        return $this->accountUserRepository->create($data);
    }
}
