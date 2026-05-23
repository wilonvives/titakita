<?php

namespace TitaKita\Http\Actions\Reports;

use TitaKita\DomainObjects\Enums\OrganizerReportTypes;
use TitaKita\DomainObjects\OrganizerDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\Report\GetOrganizerReportRequest;
use TitaKita\Services\Application\Handlers\Reports\DTO\GetOrganizerReportDTO;
use TitaKita\Services\Application\Handlers\Reports\GetOrganizerReportHandler;
use TitaKita\Services\Domain\Report\DTO\PaginatedReportDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GetOrganizerReportAction extends BaseAction
{
    public function __construct(private readonly GetOrganizerReportHandler $reportHandler)
    {
    }

    /**
     * @throws ValidationException
     */
    public function __invoke(GetOrganizerReportRequest $request, int $organizerId, string $reportType): JsonResponse
    {
        $this->isActionAuthorized($organizerId, OrganizerDomainObject::class);

        $this->validateDateRange($request);

        if (!in_array($reportType, OrganizerReportTypes::valuesArray(), true)) {
            throw new BadRequestHttpException(__('Invalid report type.'));
        }

        $reportData = $this->reportHandler->handle(
            reportData: new GetOrganizerReportDTO(
                organizerId: $organizerId,
                reportType: OrganizerReportTypes::from($reportType),
                startDate: $request->validated('start_date'),
                endDate: $request->validated('end_date'),
                currency: $request->validated('currency'),
                eventId: $request->validated('event_id'),
                page: (int) $request->validated('page', 1),
                perPage: (int) $request->validated('per_page', 1000),
            ),
        );

        if ($reportData instanceof PaginatedReportDTO) {
            return $this->jsonResponse(
                data: $reportData->toArray(),
            );
        }

        return $this->jsonResponse(
            data: $reportData,
            wrapInData: true,
        );
    }

    /**
     * @throws ValidationException
     */
    private function validateDateRange(GetOrganizerReportRequest $request): void
    {
        $startDate = $request->validated('start_date');
        $endDate = $request->validated('end_date');

        if (!$startDate || !$endDate) {
            return;
        }

        $diffInDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate));

        if ($diffInDays > 370) {
            throw ValidationException::withMessages(['start_date' => __('Date range must be less than 370 days.')]);
        }
    }
}
