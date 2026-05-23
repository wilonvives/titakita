<?php

namespace TitaKita\Services\Application\Handlers\Reports\DTO;

use TitaKita\DataTransferObjects\BaseDTO;
use TitaKita\DomainObjects\Enums\ReportTypes;

class GetReportDTO extends BaseDTO
{
    public function __construct(
        public readonly int         $eventId,
        public readonly ReportTypes $reportType,
        public readonly ?string     $startDate,
        public readonly ?string     $endDate
    )
    {
    }
}
