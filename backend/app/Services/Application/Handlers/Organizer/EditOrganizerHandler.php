<?php

namespace TitaKita\Services\Application\Handlers\Organizer;

use TitaKita\DomainObjects\ImageDomainObject;
use TitaKita\DomainObjects\OrganizerDomainObject;
use TitaKita\Repository\Interfaces\OrganizerRepositoryInterface;
use TitaKita\Services\Application\Handlers\Organizer\DTO\EditOrganizerDTO;
use TitaKita\Services\Infrastructure\HtmlPurifier\HtmlPurifierService;
use Illuminate\Database\DatabaseManager;
use Throwable;

class EditOrganizerHandler
{
    public function __construct(
        private readonly OrganizerRepositoryInterface $organizerRepository,
        private readonly DatabaseManager              $databaseManager,
        private readonly HtmlPurifierService          $htmlPurifierService,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function handle(EditOrganizerDTO $organizerData): OrganizerDomainObject
    {
        return $this->databaseManager->transaction(
            fn() => $this->editOrganizer($organizerData)
        );
    }

    private function editOrganizer(EditOrganizerDTO $organizerData): OrganizerDomainObject
    {
        $this->organizerRepository->updateWhere(
            attributes: [
                'name' => $organizerData->name,
                'email' => $organizerData->email,
                'phone' => $organizerData->phone,
                'website' => $organizerData->website,
                'description' => $this->htmlPurifierService->purify($organizerData->description),
                'account_id' => $organizerData->account_id,
                'timezone' => $organizerData->timezone,
                'currency' => $organizerData->currency,
            ],
            where: [
                'id' => $organizerData->id,
                'account_id' => $organizerData->account_id,
            ]
        );

        return $this->organizerRepository
            ->loadRelation(ImageDomainObject::class)
            ->findFirstWhere([
                'id' => $organizerData->id,
                'account_id' => $organizerData->account_id,
            ]);
    }
}
