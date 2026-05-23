<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Affiliates;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Exports\AffiliatesExport;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\DTO\QueryParamsDTO;
use TitaKita\Repository\Interfaces\AffiliateRepositoryInterface;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportAffiliatesAction extends BaseAction
{
    public function __construct(
        private readonly AffiliateRepositoryInterface $affiliateRepository,
        private readonly AffiliatesExport             $export
    )
    {
    }

    public function __invoke(int $eventId): BinaryFileResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $affiliates = $this->affiliateRepository->findByEventId($eventId, new QueryParamsDTO(
            page: 1,
            per_page: 10000,
        ));

        return Excel::download(
            $this->export->withData($affiliates),
            'affiliates.xlsx'
        );
    }
}