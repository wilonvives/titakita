<?php

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\PasswordResetTokenDomainObject;
use TitaKita\Models\PasswordResetToken;
use TitaKita\Repository\Interfaces\PasswordResetTokenRepositoryInterface;

/**
 * @extends BaseRepository<PasswordResetTokenDomainObject>
 */
class PasswordResetTokenRepository extends BaseRepository implements PasswordResetTokenRepositoryInterface
{
    protected function getModel(): string
    {
        return PasswordResetToken::class;
    }

    public function getDomainObject(): string
    {
        return PasswordResetTokenDomainObject::class;
    }
}
