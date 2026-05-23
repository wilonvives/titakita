<?php

namespace TitaKita\Repository\Interfaces;

use TitaKita\DomainObjects\MessageDomainObject;
use TitaKita\Http\DTO\QueryParamsDTO;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends RepositoryInterface<MessageDomainObject>
 */
interface MessageRepositoryInterface extends RepositoryInterface
{
    public function findByEventId(int $eventId, QueryParamsDTO $params): LengthAwarePaginator;

    public function countMessagesInLast24Hours(int $accountId): int;
}
