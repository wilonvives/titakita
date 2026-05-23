<?php

namespace TitaKita\Repository\Interfaces;

use TitaKita\DomainObjects\PromoCodeDomainObject;
use TitaKita\Http\DTO\QueryParamsDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @extends RepositoryInterface<PromoCodeDomainObject>
 */
interface PromoCodeRepositoryInterface extends RepositoryInterface
{
    public function findByEventId(int $eventId, QueryParamsDTO $params): LengthAwarePaginator;
}
