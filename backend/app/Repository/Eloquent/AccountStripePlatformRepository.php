<?php

declare(strict_types=1);

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\AccountStripePlatformDomainObject;
use TitaKita\Models\AccountStripePlatform;
use TitaKita\Repository\Interfaces\AccountStripePlatformRepositoryInterface;

/**
 * @extends BaseRepository<AccountStripePlatformDomainObject>
 */
class AccountStripePlatformRepository extends BaseRepository implements AccountStripePlatformRepositoryInterface
{
    protected function getModel(): string
    {
        return AccountStripePlatform::class;
    }

    public function getDomainObject(): string
    {
        return AccountStripePlatformDomainObject::class;
    }
}
