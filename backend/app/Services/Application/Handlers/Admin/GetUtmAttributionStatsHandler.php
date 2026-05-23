<?php

namespace TitaKita\Services\Application\Handlers\Admin;

use TitaKita\Repository\Interfaces\AccountAttributionRepositoryInterface;
use TitaKita\Services\Application\Handlers\Admin\DTO\GetUtmAttributionStatsDTO;

class GetUtmAttributionStatsHandler
{
    public function __construct(
        private readonly AccountAttributionRepositoryInterface $attributionRepository,
    )
    {
    }

    public function handle(GetUtmAttributionStatsDTO $dto): array
    {
        $stats = $this->attributionRepository->getAttributionStats(
            groupBy: $dto->group_by,
            dateFrom: $dto->date_from,
            dateTo: $dto->date_to,
            page: $dto->page,
            perPage: $dto->per_page,
        );

        $summary = $this->attributionRepository->getAttributionSummary(
            dateFrom: $dto->date_from,
            dateTo: $dto->date_to,
        );

        return [
            'data' => $stats,
            'summary' => $summary,
        ];
    }
}
