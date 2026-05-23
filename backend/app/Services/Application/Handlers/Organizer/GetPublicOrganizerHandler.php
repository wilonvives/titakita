<?php

namespace TitaKita\Services\Application\Handlers\Organizer;

use TitaKita\DomainObjects\ImageDomainObject;
use TitaKita\DomainObjects\OrganizerSettingDomainObject;
use TitaKita\Repository\Interfaces\OrganizerRepositoryInterface;

class GetPublicOrganizerHandler
{
    public function __construct(
        private readonly OrganizerRepositoryInterface $organizerRepository
    )
    {
    }

    public function handle(int $organizerId)
    {
        return $this->organizerRepository
            ->loadRelation(ImageDomainObject::class)
            ->loadRelation(OrganizerSettingDomainObject::class)
            ->findById($organizerId);
    }
}
