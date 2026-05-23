<?php

declare(strict_types=1);

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\AccountUserDomainObject;
use TitaKita\Models\AccountUser;
use TitaKita\Repository\Interfaces\AccountUserRepositoryInterface;

/**
 * @extends BaseRepository<AccountUserDomainObject>
 */
class AccountUserRepository extends BaseRepository implements AccountUserRepositoryInterface
{
    protected function getModel(): string
    {
        return AccountUser::class;
    }

    public function getDomainObject(): string
    {
        return AccountUserDomainObject::class;
    }
}
