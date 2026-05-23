<?php

namespace TitaKita\Repository\Interfaces;

use TitaKita\DomainObjects\AccountVatSettingDomainObject;

/**
 * @extends RepositoryInterface<AccountVatSettingDomainObject>
 */
interface AccountVatSettingRepositoryInterface extends RepositoryInterface
{
    public function findByAccountId(int $accountId): ?AccountVatSettingDomainObject;
}
