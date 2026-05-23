<?php

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\PasswordResetDomainObject;
use TitaKita\Models\PasswordReset;
use TitaKita\Repository\Interfaces\PasswordResetRepositoryInterface;

/**
 * @extends BaseRepository<PasswordResetDomainObject>
 */
class PasswordResetRepository extends BaseRepository implements PasswordResetRepositoryInterface
{
    protected function getModel(): string
    {
        return PasswordReset::class;
    }

    public function getDomainObject(): string
    {
        return PasswordResetDomainObject::class;
    }
}
