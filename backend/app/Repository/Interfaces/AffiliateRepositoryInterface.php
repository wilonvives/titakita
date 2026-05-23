<?php

declare(strict_types=1);

namespace TitaKita\Repository\Interfaces;

use TitaKita\DomainObjects\AffiliateDomainObject;
use TitaKita\Http\DTO\QueryParamsDTO;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends RepositoryInterface<AffiliateDomainObject>
 */
interface AffiliateRepositoryInterface extends RepositoryInterface
{
    public function findByEventId(int $eventId, QueryParamsDTO $params): LengthAwarePaginator;

    public function incrementSales(int $affiliateId, float $amount): void;
}
