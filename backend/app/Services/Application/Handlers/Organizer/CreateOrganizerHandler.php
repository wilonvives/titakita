<?php

namespace TitaKita\Services\Application\Handlers\Organizer;

use TitaKita\DomainObjects\ImageDomainObject;
use TitaKita\DomainObjects\OrganizerDomainObject;
use TitaKita\Repository\Interfaces\OrganizerRepositoryInterface;
use TitaKita\Services\Application\Handlers\Organizer\DTO\CreateOrganizerDTO;
use TitaKita\Services\Domain\Organizer\CreateDefaultOrganizerSettingsService;
use Illuminate\Database\DatabaseManager;
use Throwable;

class CreateOrganizerHandler
{
    public function __construct(
        private readonly OrganizerRepositoryInterface          $organizerRepository,
        private readonly DatabaseManager                       $databaseManager,
        private readonly CreateDefaultOrganizerSettingsService $createDefaultOrganizerSettingsService,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function handle(CreateOrganizerDTO $organizerData): OrganizerDomainObject
    {
        return $this->databaseManager->transaction(
            fn() => $this->createOrganizer($organizerData)
        );
    }

    private function createOrganizer(CreateOrganizerDTO $organizerData): OrganizerDomainObject
    {
        $organizer = $this->organizerRepository->create([
            'name' => $organizerData->name,
            'email' => $organizerData->email,
            'phone' => $organizerData->phone,
            'website' => $organizerData->website,
            'description' => $organizerData->description,
            'account_id' => $organizerData->account_id,
            'timezone' => $organizerData->timezone,
            'currency' => $organizerData->currency,
        ]);

        $this->createDefaultOrganizerSettingsService->createOrganizerSettings($organizer);

        return $this->organizerRepository
            ->loadRelation(ImageDomainObject::class)
            ->findById($organizer->getId());
    }
}
