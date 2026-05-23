<?php

namespace TitaKita\Http\Actions\Organizers\Settings;

use TitaKita\DomainObjects\OrganizerDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\Organizer\Settings\PartialUpdateOrganizerSettingsRequest;
use TitaKita\Resources\Organizer\OrganizerSettingsResource;
use TitaKita\Services\Application\Handlers\Organizer\DTO\PartialUpdateOrganizerSettingsDTO;
use TitaKita\Services\Application\Handlers\Organizer\Settings\PartialUpdateOrganizerSettingsHandler;
use Illuminate\Http\JsonResponse;

class PartialUpdateOrganizerSettingsAction extends BaseAction
{
    public function __construct(
        private readonly PartialUpdateOrganizerSettingsHandler $handler,
    )
    {
    }

    public function __invoke(PartialUpdateOrganizerSettingsRequest $request, int $organizerId): JsonResponse
    {
        $this->isActionAuthorized($organizerId, OrganizerDomainObject::class);

        $request->merge([
            'accountId' => $this->getAuthenticatedAccountId(),
            'organizerId' => $organizerId,
        ]);

        $organizerSettings = $this->handler->handle(PartialUpdateOrganizerSettingsDTO::from($request->all()));

        return $this->resourceResponse(
            resource: OrganizerSettingsResource::class,
            data: $organizerSettings,
        );
    }
}
