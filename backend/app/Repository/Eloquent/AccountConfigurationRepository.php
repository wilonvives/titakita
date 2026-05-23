<?php

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\AccountConfigurationDomainObject;
use TitaKita\Models\AccountConfiguration;
use TitaKita\Repository\Interfaces\AccountConfigurationRepositoryInterface;

/**
 * @extends BaseRepository<AccountConfigurationDomainObject>
 */
class AccountConfigurationRepository extends BaseRepository implements AccountConfigurationRepositoryInterface
{
    protected function getModel(): string
    {
        return AccountConfiguration::class;
    }

    public function getDomainObject(): string
    {
        return AccountConfigurationDomainObject::class;
    }
}
