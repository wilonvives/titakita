<?php

namespace TitaKita\Http\Actions\Organizers;

use TitaKita\DomainObjects\ImageDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Repository\Interfaces\OrganizerRepositoryInterface;
use TitaKita\Resources\Organizer\OrganizerResource;
use Illuminate\Http\JsonResponse;

class GetOrganizersAction extends BaseAction
{
    public function __construct(private readonly OrganizerRepositoryInterface $organizerRepository)
    {
    }

    public function __invoke(): JsonResponse
    {
        $organizers = $this->organizerRepository
            ->loadRelation(ImageDomainObject::class)
            ->findwhere([
                'account_id' => $this->getAuthenticatedAccountId(),
            ]);

        return $this->resourceResponse(
            resource: OrganizerResource::class,
            data: $organizers,
        );
    }
}
