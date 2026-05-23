<?php

namespace TitaKita\Http\Actions\Organizers\Settings;

use TitaKita\DomainObjects\OrganizerDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Repository\Interfaces\OrganizerSettingsRepositoryInterface;
use TitaKita\Resources\Organizer\OrganizerSettingsResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class GetOrganizerSettingsAction extends BaseAction
{
    public function __construct(private readonly OrganizerSettingsRepositoryInterface $settingsRepository)
    {
    }

    public function __invoke(int $organizerId): Response|JsonResponse
    {
        $this->isActionAuthorized($organizerId, OrganizerDomainObject::class);

        $settings = $this->settingsRepository->findFirstWhere([
            'organizer_id' => $organizerId
        ]);

        if ($settings === null) {
            return $this->notFoundResponse();
        }

        return $this->resourceResponse(OrganizerSettingsResource::class, $settings);
    }
}
