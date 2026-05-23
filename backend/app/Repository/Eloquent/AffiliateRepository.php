<?php

declare(strict_types=1);

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\AffiliateDomainObject;
use TitaKita\DomainObjects\Generated\AffiliateDomainObjectAbstract;
use TitaKita\DomainObjects\Status\AffiliateStatus;
use TitaKita\Http\DTO\QueryParamsDTO;
use TitaKita\Models\Affiliate;
use TitaKita\Repository\Interfaces\AffiliateRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends BaseRepository<AffiliateDomainObject>
 */
class AffiliateRepository extends BaseRepository implements AffiliateRepositoryInterface
{
    protected function getModel(): string
    {
        return Affiliate::class;
    }

    public function getDomainObject(): string
    {
        return AffiliateDomainObject::class;
    }

    public function findByEventId(int $eventId, QueryParamsDTO $params): LengthAwarePaginator
    {
        $where = [
            [AffiliateDomainObjectAbstract::EVENT_ID, '=', $eventId]
        ];

        if ($params->query) {
            $where[] = static function (Builder $builder) use ($params) {
                $builder
                    ->orWhere(AffiliateDomainObjectAbstract::NAME, 'ilike', '%' . $params->query . '%')
                    ->orWhere(AffiliateDomainObjectAbstract::CODE, 'ilike', '%' . $params->query . '%')
                    ->orWhere(AffiliateDomainObjectAbstract::EMAIL, 'ilike', '%' . $params->query . '%');
            };
        }

        $this->model = $this->model->orderBy(
            column: $this->validateSortColumn($params->sort_by, AffiliateDomainObject::class),
            direction: $this->validateSortDirection($params->sort_direction, AffiliateDomainObject::class),
        );

        return $this->paginateWhere(
            where: $where,
            limit: $params->per_page,
            page: $params->page,
        );
    }

    public function findByCodeAndEventId(string $code, int $eventId): ?AffiliateDomainObject
    {
        return $this->findFirstWhere([
            AffiliateDomainObjectAbstract::CODE => $code,
            AffiliateDomainObjectAbstract::EVENT_ID => $eventId,
            AffiliateDomainObjectAbstract::STATUS => AffiliateStatus::ACTIVE->value,
        ]);
    }

    public function incrementSales(int $affiliateId, float $amount): void
    {
        $this->model->where('id', $affiliateId)
            ->increment('total_sales', 1, [
                'total_sales_gross' => $this->db->raw('total_sales_gross + ' . $amount)
            ]);
    }
}
