<?php

namespace TitaKita\Repository\Interfaces;

use TitaKita\DomainObjects\WebhookDomainObject;
use Illuminate\Support\Collection;

/**
 * @extends RepositoryInterface<WebhookDomainObject>
 */
interface WebhookRepositoryInterface extends RepositoryInterface
{
    public function findEnabledByEventId(int $eventId): Collection;
}
