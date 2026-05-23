<?php

namespace TitaKita\Services\Domain\Report\Factory;

use TitaKita\DomainObjects\Enums\OrganizerReportTypes;
use TitaKita\Services\Domain\Report\AbstractOrganizerReportService;
use TitaKita\Services\Domain\Report\OrganizerReports\CheckInSummaryReport;
use TitaKita\Services\Domain\Report\OrganizerReports\EventsPerformanceReport;
use TitaKita\Services\Domain\Report\OrganizerReports\PlatformFeesReport;
use TitaKita\Services\Domain\Report\OrganizerReports\RevenueSummaryReport;
use TitaKita\Services\Domain\Report\OrganizerReports\TaxSummaryReport;
use Illuminate\Support\Facades\App;

class OrganizerReportServiceFactory
{
    public function create(OrganizerReportTypes $reportType): AbstractOrganizerReportService|PlatformFeesReport
    {
        return match ($reportType) {
            OrganizerReportTypes::REVENUE_SUMMARY => App::make(RevenueSummaryReport::class),
            OrganizerReportTypes::EVENTS_PERFORMANCE => App::make(EventsPerformanceReport::class),
            OrganizerReportTypes::TAX_SUMMARY => App::make(TaxSummaryReport::class),
            OrganizerReportTypes::CHECK_IN_SUMMARY => App::make(CheckInSummaryReport::class),
            OrganizerReportTypes::PLATFORM_FEES => App::make(PlatformFeesReport::class),
        };
    }
}
