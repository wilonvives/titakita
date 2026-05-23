<?php

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\TicketLookupTokenDomainObject;
use TitaKita\Models\TicketLookupToken;
use TitaKita\Repository\Interfaces\TicketLookupTokenRepositoryInterface;

/**
 * @extends BaseRepository<TicketLookupTokenDomainObject>
 */
class TicketLookupTokenRepository extends BaseRepository implements TicketLookupTokenRepositoryInterface
{
    protected function getModel(): string
    {
        return TicketLookupToken::class;
    }

    public function getDomainObject(): string
    {
        return TicketLookupTokenDomainObject::class;
    }
}
