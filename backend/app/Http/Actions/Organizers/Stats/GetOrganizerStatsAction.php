<?php

namespace TitaKita\Http\Actions\Organizers\Stats;

use TitaKita\DomainObjects\OrganizerDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Services\Application\Handlers\Organizer\DTO\GetOrganizerStatsRequestDTO;
use TitaKita\Services\Application\Handlers\Organizer\GetOrganizerStatsHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetOrganizerStatsAction extends BaseAction
{
    public function __construct(
        private readonly GetOrganizerStatsHandler $getOrganizerStatsHandler,
    )
    {
    }

    public function __invoke(Request $request, int $organizerId): JsonResponse
    {
        $this->isActionAuthorized($organizerId, OrganizerDomainObject::class);

        $organizerStats = $this->getOrganizerStatsHandler->handle(new GetOrganizerStatsRequestDTO(
            organizerId: $organizerId,
            accountId: $this->getAuthenticatedAccountId(),
            currencyCode: $request->get('currency_code'),
        ));

        return $this->jsonResponse(
            data: $organizerStats,
            wrapInData: true,
        );
    }
}
