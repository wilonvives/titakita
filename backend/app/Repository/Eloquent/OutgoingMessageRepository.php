<?php

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\OutgoingMessageDomainObject;
use TitaKita\Models\OutgoingMessage;
use TitaKita\Repository\Interfaces\OutgoingMessageRepositoryInterface;

/**
 * @extends BaseRepository<OutgoingMessageDomainObject>
 */
class OutgoingMessageRepository extends BaseRepository implements OutgoingMessageRepositoryInterface
{
    protected function getModel(): string
    {
        return OutgoingMessage::class;
    }

    public function getDomainObject(): string
    {
        return OutgoingMessageDomainObject::class;
    }
}
