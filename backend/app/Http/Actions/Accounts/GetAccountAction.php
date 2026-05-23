<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Accounts;

use TitaKita\DomainObjects\AccountConfigurationDomainObject;
use TitaKita\DomainObjects\AccountStripePlatformDomainObject;
use TitaKita\DomainObjects\Enums\Role;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Repository\Eloquent\Value\Relationship;
use TitaKita\Repository\Interfaces\AccountRepositoryInterface;
use TitaKita\Resources\Account\AccountResource;
use Illuminate\Http\JsonResponse;

class GetAccountAction extends BaseAction
{
    protected AccountRepositoryInterface $accountRepository;

    public function __construct(AccountRepositoryInterface $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    public function __invoke(?int $accountId = null): JsonResponse
    {
        $this->minimumAllowedRole(Role::ORGANIZER);

        $account = $this->accountRepository
            ->loadRelation(new Relationship(
                domainObject: AccountConfigurationDomainObject::class,
                name: 'configuration',
            ))
            ->loadRelation(AccountStripePlatformDomainObject::class)
            ->findById($this->getAuthenticatedAccountId());

        return $this->resourceResponse(AccountResource::class, $account);
    }
}
