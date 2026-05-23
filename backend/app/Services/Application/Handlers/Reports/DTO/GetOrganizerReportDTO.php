<?php

namespace TitaKita\Services\Application\Handlers\Reports\DTO;

use TitaKita\DataTransferObjects\BaseDataObject;
use TitaKita\DomainObjects\Enums\OrganizerReportTypes;

class GetOrganizerReportDTO extends BaseDataObject
{
    public function __construct(
        public readonly int                   $organizerId,
        public readonly OrganizerReportTypes  $reportType,
        public readonly ?string               $startDate,
        public readonly ?string               $endDate,
        public readonly ?string               $currency,
        public readonly ?int                  $eventId = null,
        public readonly int                   $page = 1,
        public readonly int                   $perPage = 1000,
    )
    {
    }
}
