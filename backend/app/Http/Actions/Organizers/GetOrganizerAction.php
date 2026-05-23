<?php

namespace TitaKita\Http\Actions\Organizers;

use TitaKita\DomainObjects\ImageDomainObject;
use TitaKita\DomainObjects\OrganizerDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Repository\Interfaces\OrganizerRepositoryInterface;
use TitaKita\Resources\Organizer\OrganizerResource;
use Symfony\Component\HttpFoundation\Response;

class GetOrganizerAction extends BaseAction
{
    public function __construct(private readonly OrganizerRepositoryInterface $organizerRepository)
    {
    }

    public function __invoke(int $organizerId): Response
    {
        $this->isActionAuthorized(
            entityId: $organizerId,
            entityType: OrganizerDomainObject::class,
        );

        $organizer = $this->organizerRepository
            ->loadRelation(ImageDomainObject::class)
            ->findFirstWhere([
                'id' => $organizerId,
                'account_id' => $this->getAuthenticatedAccountId(),
            ]);

        if ($organizer === null) {
            return $this->notFoundResponse();
        }

        return $this->resourceResponse(
            resource: OrganizerResource::class,
            data: $organizer,
        );
    }
}
