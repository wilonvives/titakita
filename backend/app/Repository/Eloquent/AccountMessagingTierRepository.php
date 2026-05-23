<?php

declare(strict_types=1);

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\AccountMessagingTierDomainObject;
use TitaKita\Models\AccountMessagingTier;
use TitaKita\Repository\Interfaces\AccountMessagingTierRepositoryInterface;

/**
 * @extends BaseRepository<AccountMessagingTierDomainObject>
 */
class AccountMessagingTierRepository extends BaseRepository implements AccountMessagingTierRepositoryInterface
{
    protected function getModel(): string
    {
        return AccountMessagingTier::class;
    }

    public function getDomainObject(): string
    {
        return AccountMessagingTierDomainObject::class;
    }
}
