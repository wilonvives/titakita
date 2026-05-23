<?php

declare(strict_types=1);

namespace TitaKita\Repository\Interfaces;

use TitaKita\DomainObjects\AccountAttributionDomainObject;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends RepositoryInterface<AccountAttributionDomainObject>
 */
interface AccountAttributionRepositoryInterface extends RepositoryInterface
{
    public function getAttributionStats(
        string $groupBy,
        ?string $dateFrom,
        ?string $dateTo,
        int $perPage,
        int $page
    ): LengthAwarePaginator;

    public function getAttributionSummary(?string $dateFrom, ?string $dateTo): array;
}
